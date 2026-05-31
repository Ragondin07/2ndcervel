#!/usr/bin/env bash
set -Eeuo pipefail
source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/scripts/lib/admin-common.sh"

main() {
    cd_root
    section "Mise à jour applicative"
    require_docker
    ensure_env
    backup_env
    fix_permissions_host
    build_if_needed

    info "Démarrage/relance des conteneurs..."
    compose_cmd up -d
    wait_for_service_healthy "$POSTGRES_SERVICE" 120

    section "Maintenance Laravel"
    artisan migrate --force
    artisan optimize:clear

    section "Workers"
    restart_workers

    section "Vérifications"
    laravel_healthcheck
    verify_permissions

    section "Résumé"
    compose_cmd ps
    success "Mise à jour terminée sans suppression de données."
}

main "$@"
