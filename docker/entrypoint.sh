#!/bin/bash
set -e

cd /var/www/html

# ── Generate APP_KEY if not set ────────────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] Generating APP_KEY..."
    php artisan key:generate --force
fi

# ── Ensure writable directories exist ─────────────────────────────────────
mkdir -p storage/framework/sessions \
         storage/framework/views \
         storage/framework/cache/data \
         storage/logs \
         bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ── Storage symlink ────────────────────────────────────────────────────────
if [ ! -L public/storage ]; then
    php artisan storage:link --quiet
fi

# ── Wait for MySQL port to be reachable ────────────────────────────────────
_DB_HOST="${DB_HOST:-127.0.0.1}"
_DB_PORT="${DB_PORT:-3306}"

echo "[entrypoint] Waiting for database at ${_DB_HOST}:${_DB_PORT}..."
RETRIES=30
until (echo > /dev/tcp/"${_DB_HOST}"/"${_DB_PORT}") 2>/dev/null; do
    echo "[entrypoint] Database not ready — retrying in 2s ($RETRIES left)..."
    sleep 2
    RETRIES=$((RETRIES - 1))
    if [ "$RETRIES" -eq 0 ]; then
        echo "[entrypoint] ERROR: Could not reach database after 60s. Exiting."
        exit 1
    fi
done
echo "[entrypoint] Database port open. Waiting 1s for MySQL to finish init..."
sleep 1

# ── Run migrations ─────────────────────────────────────────────────────────
echo "[entrypoint] Running migrations..."
php artisan migrate --force

# ── Cache config/routes/views for production ──────────────────────────────
if [ "${APP_ENV}" = "production" ]; then
    echo "[entrypoint] Caching config, routes, views..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo "[entrypoint] Starting services via supervisord..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
