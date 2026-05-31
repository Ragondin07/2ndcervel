#!/usr/bin/env bash
set -Eeuo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
COMPOSE_FILE="$PROJECT_ROOT/docker-compose.yml"
ENV_FILE="$PROJECT_ROOT/.env"
ENV_EXAMPLE="$PROJECT_ROOT/.env.example"
BACKUP_DIR="$PROJECT_ROOT/backups"
BUILD_STAMP="$PROJECT_ROOT/.docker-build.sha256"

APP_SERVICE="app"
WORKER_SERVICE="worker"
POSTGRES_SERVICE="postgres"
MEILI_SERVICE="meilisearch"
TIKA_SERVICE="tika"

info() { printf '\033[1;34m[INFO]\033[0m %s\n' "$*"; }
success() { printf '\033[1;32m[OK]\033[0m %s\n' "$*"; }
warn() { printf '\033[1;33m[WARN]\033[0m %s\n' "$*"; }
error() { printf '\033[1;31m[ERR]\033[0m %s\n' "$*" >&2; }
section() { printf '\n\033[1m== %s ==\033[0m\n' "$*"; }

cd_root() { cd "$PROJECT_ROOT"; }

compose_cmd() {
    if docker compose version >/dev/null 2>&1; then
        docker compose "$@"
    elif command -v docker-compose >/dev/null 2>&1; then
        docker-compose "$@"
    else
        error "Docker Compose est introuvable (plugin 'docker compose' ou binaire 'docker-compose')."
        return 1
    fi
}

require_docker() {
    command -v docker >/dev/null 2>&1 || { error "Docker est introuvable."; return 1; }
    docker info >/dev/null 2>&1 || { error "Le daemon Docker ne répond pas."; return 1; }
    compose_cmd version >/dev/null
}

ensure_env() {
    if [ ! -f "$ENV_FILE" ]; then
        [ -f "$ENV_EXAMPLE" ] || { error ".env manquant et .env.example introuvable."; return 1; }
        cp "$ENV_EXAMPLE" "$ENV_FILE"
        warn ".env créé depuis .env.example : vérifiez les secrets avant production."
    fi
}

backup_env() {
    ensure_env
    mkdir -p "$BACKUP_DIR/env"
    local target="$BACKUP_DIR/env/.env.$(date +%Y%m%d-%H%M%S).bak"
    cp -p "$ENV_FILE" "$target"
    success "Sauvegarde .env : $target"
}

app_url() {
    local url
    url=$(awk -F= '/^APP_URL=/{print substr($0, index($0,"=")+1)}' "$ENV_FILE" 2>/dev/null | tail -n1 | sed 's/^"//;s/"$//')
    printf '%s' "${url:-http://localhost:8000}"
}

build_fingerprint() {
    cd_root
    { [ -f Dockerfile ] && sha256sum Dockerfile; [ -f docker-compose.yml ] && sha256sum docker-compose.yml; [ -f docker/app/entrypoint.sh ] && sha256sum docker/app/entrypoint.sh; [ -f composer.json ] && sha256sum composer.json; [ -f composer.lock ] && sha256sum composer.lock; } | sha256sum | awk '{print $1}'
}

image_exists() {
    local image_id
    image_id=$(compose_cmd images -q "$APP_SERVICE" 2>/dev/null | head -n1 || true)
    [ -n "$image_id" ]
}

build_if_needed() {
    cd_root
    local current previous=""
    current=$(build_fingerprint)
    [ -f "$BUILD_STAMP" ] && previous=$(cat "$BUILD_STAMP")

    if [ "$current" != "$previous" ] || ! image_exists; then
        info "Image absente ou configuration modifiée : reconstruction Docker avec cache."
        compose_cmd build "$APP_SERVICE" "$WORKER_SERVICE"
        printf '%s\n' "$current" > "$BUILD_STAMP"
        success "Images Docker à jour."
    else
        success "Aucune reconstruction nécessaire."
    fi
}

wait_for_service_healthy() {
    local service="$1" timeout="${2:-90}" elapsed=0 id status
    info "Attente du service $service..."
    while [ "$elapsed" -lt "$timeout" ]; do
        id=$(compose_cmd ps -q "$service" 2>/dev/null || true)
        if [ -n "$id" ]; then
            status=$(docker inspect -f '{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}' "$id" 2>/dev/null || true)
            if [ "$status" = "healthy" ] || [ "$status" = "running" ]; then
                success "$service prêt ($status)."
                return 0
            fi
        fi
        sleep 2; elapsed=$((elapsed + 2))
    done
    error "$service indisponible après ${timeout}s."
    return 1
}

artisan() { compose_cmd exec -T "$APP_SERVICE" php artisan "$@"; }

container_running() {
    [ "$(compose_cmd ps -q "$1" 2>/dev/null | wc -l | tr -d ' ')" != "0" ] && [ "$(compose_cmd ps --status running -q "$1" 2>/dev/null | wc -l | tr -d ' ')" != "0" ]
}

laravel_healthcheck() {
    local url="$(app_url)/up"
    if curl -fsS --max-time 10 "$url" >/dev/null; then
        success "Laravel répond : $url"
        return 0
    fi
    error "Laravel ne répond pas : $url"
    return 1
}

recent_laravel_errors() {
    local log_file="$PROJECT_ROOT/storage/logs/laravel.log"
    if [ -f "$log_file" ]; then
        grep -Ei "(error|exception|critical|alert|emergency)" "$log_file" | tail -n 20 || true
    else
        warn "storage/logs/laravel.log introuvable sur l'hôte."
    fi
}

fix_permissions_host() {
    cd_root
    mkdir -p storage/app/uploads storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
    chmod -R ug+rwX storage bootstrap/cache
    find storage bootstrap/cache -type d -exec chmod 775 {} +
    find storage bootstrap/cache -type f -exec chmod 664 {} +
}

fix_permissions_container() {
    if container_running "$APP_SERVICE"; then
        compose_cmd exec -T "$APP_SERVICE" sh -lc 'mkdir -p storage/app/uploads storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache && chmod -R ug+rwX storage bootstrap/cache && find storage bootstrap/cache -type d -exec chmod 775 {} + && find storage bootstrap/cache -type f -exec chmod 664 {} +'
    fi
}

verify_permissions() {
    local failed=0 path
    for path in storage storage/app/uploads bootstrap/cache; do
        if [ -d "$PROJECT_ROOT/$path" ] && [ -w "$PROJECT_ROOT/$path" ]; then
            success "$path existe et est inscriptible."
        else
            error "$path absent ou non inscriptible."
            failed=1
        fi
    done
    return "$failed"
}

restart_workers() {
    if container_running "$APP_SERVICE"; then
        artisan queue:restart || warn "Impossible de signaler queue:restart."
    fi
    compose_cmd up -d --no-deps --force-recreate "$WORKER_SERVICE"
}

postgres_dump() {
    mkdir -p "$BACKUP_DIR/postgres"
    local file="$BACKUP_DIR/postgres/postgres.$(date +%Y%m%d-%H%M%S).sql"
    compose_cmd exec -T "$POSTGRES_SERVICE" sh -lc 'pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB"' > "$file"
    success "Sauvegarde PostgreSQL : $file"
}

uploads_backup() {
    mkdir -p "$BACKUP_DIR/uploads"
    local file="$BACKUP_DIR/uploads/uploads.$(date +%Y%m%d-%H%M%S).tar.gz"
    tar -czf "$file" -C "$PROJECT_ROOT/storage/app" uploads 2>/dev/null || tar -czf "$file" -C "$PROJECT_ROOT" storage/app/uploads
    success "Sauvegarde uploads : $file"
}

full_backup() {
    section "Sauvegarde"
    backup_env
    postgres_dump
    uploads_backup
}
