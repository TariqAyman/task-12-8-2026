#!/bin/sh
set -e

# Wait for the database to accept connections before touching it.
wait_for_database() {
    attempt=1
    max_attempts=30

    until php artisan db:show >/dev/null 2>&1; do
        if [ "$attempt" -ge "$max_attempts" ]; then
            echo "Database is still unreachable after ${max_attempts} attempts, continuing anyway."
            return 0
        fi

        echo "Waiting for the database (attempt ${attempt}/${max_attempts})..."
        attempt=$((attempt + 1))
        sleep 2
    done

    echo "Database is ready."
}

# The application key normally arrives as an environment variable from docker
# compose. Generating it here is only a fallback so the container still boots;
# because it is not persisted, anything encrypted with it is invalidated by a
# restart.
if [ -z "${APP_KEY}" ]; then
    echo "APP_KEY is not set, generating an ephemeral one..."
    APP_KEY="$(php artisan key:generate --show --no-interaction)"
    export APP_KEY
fi

wait_for_database

echo "Running database migrations..."
php artisan migrate --force --no-interaction

echo "Generating the OpenAPI documentation..."
php artisan l5-swagger:generate

# Routes are intentionally not cached: the starter kit registers a closure based
# route, which Laravel cannot serialize.
echo "Caching configuration and events..."
php artisan config:cache
php artisan event:cache

exec "$@"
