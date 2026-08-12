# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1 — install PHP dependencies and build the bundled frontend assets.
#
# The Vite build shells out to `php artisan wayfinder:generate`, so this stage
# needs both PHP (from the composer image) and Node.
# ---------------------------------------------------------------------------
FROM composer:2 AS build

WORKDIR /app

RUN apk add --no-cache nodejs npm

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN cp -n .env.example .env \
    && composer dump-autoload --optimize --no-dev \
    && npm run build

# ---------------------------------------------------------------------------
# Stage 2 — runtime
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php8.3 AS runtime

ENV APP_ENV=production \
    APP_DEBUG=false \
    SERVER_NAME=:8000

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions \
        pdo_mysql \
        redis \
        intl \
        zip \
        bcmath \
        opcache \
        pcntl

COPY --chown=www-data:www-data . /app
COPY --from=build --chown=www-data:www-data /app/vendor /app/vendor
COPY --from=build --chown=www-data:www-data /app/public/build /app/public/build
COPY docker/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
        storage/logs storage/api-docs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=5 \
    CMD curl --fail http://localhost:8000/up || exit 1

ENTRYPOINT ["entrypoint"]

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
