#!/usr/bin/env sh
set -eu

APP_DIR=${APP_DIR:-/var/www/html}
ENV_FILE=${ENV_FILE:-.env}

cd "$APP_DIR"

copy_env_example_if_missing() {
    if [ ! -f "$ENV_FILE" ]; then
        if [ ! -f .env.example ]; then
            echo "ERROR: $ENV_FILE is missing and .env.example is unavailable." >&2
            exit 1
        fi

        echo "Creating $ENV_FILE from .env.example. Review secrets before production use." >&2
        cp .env.example "$ENV_FILE"
    fi
}

app_key_value() {
    awk -F= '/^APP_KEY=/{print substr($0, index($0, "=") + 1); exit}' "$ENV_FILE"
}

validate_app_key() {
    app_key=$(app_key_value)

    if [ -z "$app_key" ]; then
        return 0
    fi

    base64_count=$(printf '%s' "$app_key" | awk '{ print gsub(/base64:/, "") }')

    if [ "$base64_count" -gt 1 ]; then
        echo "ERROR: $ENV_FILE contains a corrupted APP_KEY with multiple base64: prefixes." >&2
        echo "Fix it once from a backup or run: php artisan key:generate --force (only if no encrypted data depends on the old key)." >&2
        exit 1
    fi

    if ! printf '%s' "$app_key" | grep -Eq '^base64:[A-Za-z0-9+/=]+$'; then
        echo "ERROR: $ENV_FILE contains a non-empty APP_KEY that is not a valid base64 Laravel key." >&2
        echo "The entrypoint will not overwrite an existing APP_KEY automatically." >&2
        exit 1
    fi
}

generate_app_key_if_missing() {
    app_key=$(app_key_value)

    if [ -n "$app_key" ]; then
        echo "APP_KEY already configured; leaving $ENV_FILE unchanged." >&2
        return 0
    fi

    echo "APP_KEY is empty; generating it once." >&2
    php artisan key:generate --force --no-interaction
}

install_dependencies_if_missing() {
    if [ -d vendor ] && [ -f vendor/autoload.php ]; then
        return 0
    fi

    if [ ! -f composer.lock ]; then
        echo "ERROR: composer.lock is missing. Dependencies must be installed from the committed lock file." >&2
        exit 1
    fi

    composer install --no-interaction --prefer-dist --no-progress
}

prepare_laravel_directories() {
    mkdir -p \
        storage/app/uploads \
        storage/app/private \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache

    chmod -R ug+rwX storage bootstrap/cache
}

copy_env_example_if_missing
validate_app_key
install_dependencies_if_missing
generate_app_key_if_missing
validate_app_key
prepare_laravel_directories

exec "$@"
