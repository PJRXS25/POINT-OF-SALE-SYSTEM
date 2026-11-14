FROM dunglas/frankenphp:1-php8.4-alpine AS base

WORKDIR /app

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && install-php-extensions intl mbstring mysqli opcache

FROM base AS dependencies

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apk add --no-cache git unzip

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

FROM base AS runtime

COPY --from=dependencies /app/vendor ./vendor
COPY . .
COPY Caddyfile /etc/caddy/Caddyfile

RUN mkdir -p \
        writable/cache \
        writable/logs \
        writable/session \
        writable/uploads \
        writable/debugbar \
        public/uploads/products \
        public/uploads/avatars \
    && chown -R www-data:www-data writable public/uploads

ENV CI_ENVIRONMENT=production
ENV cookie_secure=true
ENV media_storage=database
ENV PORT=8080

EXPOSE 8080

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
