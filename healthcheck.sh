#!/usr/bin/env bash
set -Eeuo pipefail
source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/scripts/lib/admin-common.sh"

status_line() {
    local label="$1"; shift
    if "$@" >/dev/null 2>&1; then success "$label"; else error "$label"; fi
}

main() {
    cd_root
    section "Diagnostic système"
    require_docker
    ensure_env

    section "Conteneurs"
    compose_cmd ps

    section "PostgreSQL"
    status_line "PostgreSQL accepte les connexions" compose_cmd exec -T "$POSTGRES_SERVICE" sh -lc 'pg_isready -U "$POSTGRES_USER" -d "$POSTGRES_DB"'

    section "Meilisearch"
    status_line "Meilisearch répond" compose_cmd exec -T "$APP_SERVICE" sh -lc 'curl -fsS "${MEILISEARCH_HOST:-http://meilisearch:7700}/health" | grep -q available'

    section "Tika"
    status_line "Tika répond" compose_cmd exec -T "$APP_SERVICE" sh -lc 'curl -fsS "${TIKA_URL:-http://tika:9998}/tika" >/dev/null'

    section "Laravel"
    laravel_healthcheck || true
    if container_running "$APP_SERVICE"; then
        artisan about --only=environment || true
        artisan migrate:status || true
    fi

    section "Workers"
    if container_running "$WORKER_SERVICE"; then success "worker en cours d'exécution"; else error "worker arrêté"; fi
    if container_running "$APP_SERVICE"; then
        compose_cmd exec -T "$APP_SERVICE" php artisan queue:monitor indexing,default --max=100 || true
    fi

    section "Permissions"
    verify_permissions || true

    section "Espace disque"
    df -h "$PROJECT_ROOT"
    docker system df || true

    section "Erreurs Laravel récentes"
    recent_laravel_errors || true
}

main "$@"
