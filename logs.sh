#!/usr/bin/env bash
set -Eeuo pipefail
source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/scripts/lib/admin-common.sh"

tail_laravel() {
    local file="$PROJECT_ROOT/storage/logs/laravel.log"
    if [ -f "$file" ]; then tail -n "${1:-200}" "$file"; else warn "laravel.log introuvable."; fi
}

errors_only() {
    recent_laravel_errors
    compose_cmd logs --tail=300 app worker postgres meilisearch tika | grep -Ei "(error|exception|fatal|critical|panic|failed)" | tail -n 100 || true
}

menu() {
    cat <<'MENU'

== Logs ==
1. Logs application
2. Logs worker
3. Logs PostgreSQL
4. Logs Meilisearch
5. Logs Tika
6. laravel.log
7. erreurs récentes uniquement
8. suivi temps réel
MENU
    printf 'Choix: '
}

main() {
    cd_root; require_docker
    local choice="${1:-}"
    if [ -z "$choice" ]; then menu; read -r choice; fi
    case "$choice" in
        1) compose_cmd logs --tail=200 app ;;
        2) compose_cmd logs --tail=200 worker ;;
        3) compose_cmd logs --tail=200 postgres ;;
        4) compose_cmd logs --tail=200 meilisearch ;;
        5) compose_cmd logs --tail=200 tika ;;
        6) tail_laravel 200 ;;
        7) errors_only ;;
        8) compose_cmd logs -f --tail=100 app worker postgres meilisearch tika ;;
        *) error "Choix invalide"; exit 1 ;;
    esac
}
main "$@"
