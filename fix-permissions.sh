#!/usr/bin/env bash
set -Eeuo pipefail
source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/scripts/lib/admin-common.sh"

main() {
    cd_root
    section "Réparation permissions Laravel"
    fix_permissions_host
    fix_permissions_container || true
    verify_permissions
    success "Permissions réparées."
}
main "$@"
