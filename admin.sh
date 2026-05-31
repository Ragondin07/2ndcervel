#!/usr/bin/env bash
set -Eeuo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$ROOT/scripts/lib/admin-common.sh"

restore_postgres() {
    require_docker
    local file="${1:-}"
    if [ -z "$file" ]; then
        section "Sauvegardes disponibles"
        find "$BACKUP_DIR/postgres" -maxdepth 1 -type f -name '*.sql' 2>/dev/null | sort || true
        printf 'Fichier SQL à restaurer: '; read -r file
    fi
    [ -f "$file" ] || { error "Fichier introuvable: $file"; return 1; }
    warn "Restauration PostgreSQL depuis $file. Les données DB actuelles seront remplacées."
    printf 'Tapez RESTORE pour confirmer: '; read -r confirm
    [ "$confirm" = "RESTORE" ] || { warn "Restauration annulée."; return 0; }
    compose_cmd exec -T "$POSTGRES_SERVICE" sh -lc 'dropdb -U "$POSTGRES_USER" "$POSTGRES_DB" --if-exists && createdb -U "$POSTGRES_USER" "$POSTGRES_DB"'
    compose_cmd exec -T "$POSTGRES_SERVICE" sh -lc 'psql -U "$POSTGRES_USER" "$POSTGRES_DB"' < "$file"
    success "Restauration PostgreSQL terminée."
}

restore_uploads() {
    local file="${1:-}"
    if [ -z "$file" ]; then
        section "Archives uploads disponibles"
        find "$BACKUP_DIR/uploads" -maxdepth 1 -type f -name '*.tar.gz' 2>/dev/null | sort || true
        printf 'Archive uploads à restaurer: '; read -r file
    fi
    [ -f "$file" ] || { error "Archive introuvable: $file"; return 1; }
    warn "Restauration uploads depuis $file. Les fichiers existants portant le même nom seront remplacés."
    printf 'Tapez UPLOADS pour confirmer: '; read -r confirm
    [ "$confirm" = "UPLOADS" ] || { warn "Restauration uploads annulée."; return 0; }
    mkdir -p "$PROJECT_ROOT/storage/app"
    tar -xzf "$file" -C "$PROJECT_ROOT/storage/app"
    fix_permissions_host
    fix_permissions_container || true
    success "Restauration uploads terminée."
}

restore_menu() {
    cat <<'MENU'

== Restauration ==
1. PostgreSQL
2. Uploads
3. Annuler
MENU
    printf 'Choix: '; read -r restore_choice
    case "$restore_choice" in
        1) restore_postgres ;;
        2) restore_uploads ;;
        *) warn "Restauration annulée." ;;
    esac
}

pending_ocr() {
    require_docker
    artisan tinker --execute='echo App\Models\File::query()->whereIn("ocr_status", ["en_attente", "en_cours", "erreur"])->count().PHP_EOL;' || true
}

restart_services() {
    require_docker
    compose_cmd restart app worker postgres meilisearch tika
    wait_for_service_healthy "$POSTGRES_SERVICE" 120 || true
    laravel_healthcheck || true
}

clear_logs() {
    section "Nettoyage logs"
    mkdir -p "$PROJECT_ROOT/storage/logs"
    find "$PROJECT_ROOT/storage/logs" -type f -name '*.log' -print -exec sh -c ': > "$1"' _ {} \;
    success "Logs Laravel vidés sans supprimer les fichiers."
}

full_diagnostic() {
    "$ROOT/healthcheck.sh"
    section "Routes Laravel"
    artisan route:list || true
    section "Migrations"
    artisan migrate:status || true
    section "Configuration Docker"
    compose_cmd config --quiet && success "docker-compose.yml valide"
}

menu() {
    cat <<'MENU'

== Console administration 2nd CERVEL ==
1. Mise à jour
2. État du système
3. Logs
4. Sauvegarde
5. Restauration
6. Réindexation Meilisearch
7. Traitements OCR en attente/erreur
8. Vérification permissions
9. Redémarrage services
10. Diagnostic complet
11. Nettoyage caches Laravel
12. Nettoyage logs
13. Reconstruction index complet
14. Quitter
MENU
    printf 'Choix: '
}

main() {
    cd_root
    local choice="${1:-}"
    while true; do
        if [ -z "$choice" ]; then menu; read -r choice; fi
        case "$choice" in
            1) "$ROOT/update.sh" ;;
            2) "$ROOT/healthcheck.sh" ;;
            3) "$ROOT/logs.sh" ;;
            4) require_docker; full_backup ;;
            5) restore_menu ;;
            6) require_docker; artisan scout:sync-index-settings; artisan search:reindex ;;
            7) pending_ocr ;;
            8) "$ROOT/fix-permissions.sh" ;;
            9) restart_services ;;
            10) full_diagnostic ;;
            11) require_docker; artisan optimize:clear ;;
            12) clear_logs ;;
            13) require_docker; artisan scout:flush 'App\Models\File' || true; artisan search:reindex ;;
            14|q|quit|exit) success "Au revoir."; exit 0 ;;
            *) error "Choix invalide" ;;
        esac
        [ -n "${1:-}" ] && break
        choice=""
    done
}
main "$@"
