FROM php:8.3-cli-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        curl \
        git \
        icu-libs \
        imagemagick \
        libpq \
        libzip \
        oniguruma \
        poppler-utils \
        sqlite-libs \
        tesseract-ocr \
        tesseract-ocr-data-eng \
        tesseract-ocr-data-fra \
        unzip \
        zip \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
        sqlite-dev \
    && docker-php-ext-install \
        intl \
        mbstring \
        pdo \
        pdo_pgsql \
        pdo_sqlite \
        zip \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./
RUN if [ -f composer.lock ]; then \
        composer install --no-interaction --prefer-dist --no-scripts --no-progress; \
    else \
        echo "ERROR: composer.lock is missing. Run composer update in a networked environment and commit composer.lock." >&2; \
        exit 1; \
    fi

COPY . .
COPY docker/app/entrypoint.sh /usr/local/bin/app-entrypoint

RUN chmod +x /usr/local/bin/app-entrypoint \
    && mkdir -p storage/app/uploads storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 8000

ENTRYPOINT ["app-entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
