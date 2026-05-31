#!/usr/bin/env bash
set -Eeuo pipefail
source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/scripts/lib/admin-common.sh"

main() {
    cd_root
    section "Réinitialisation développement"
    require_docker
    ensure_env
    local env_name
    env_name=$(awk -F= '/^APP_ENV=/{print substr($0,index($0,"=")+1)}' "$ENV_FILE" | tail -n1 | tr -d '"')
    if [ "$env_name" = "production" ]; then
        error "Refus : dev-reset.sh ne s'exécute pas avec APP_ENV=production."
        exit 1
    fi
    backup_env
    uploads_backup || true
    compose_cmd up -d postgres meilisearch tika app worker
    wait_for_service_healthy "$POSTGRES_SERVICE" 120
    artisan migrate:fresh --seed --force
    artisan scout:sync-index-settings || true
    artisan search:reindex || true
    restart_workers
    laravel_healthcheck || true
    section "Résumé"
    success "Base recréée, seeders exécutés, index Meilisearch reconstruit. Les uploads ont été conservés."
}
main "$@"
