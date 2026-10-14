#!/bin/sh
set -e

# Default Render port is 10000 if PORT is not set
export PORT="${PORT:-10000}"

echo "Configuring Nginx to listen on port ${PORT}..."
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

# Ensure all Laravel runtime storage directories exist
mkdir -p /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Laravel storage permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear any stale cached configuration
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Cache configuration, routes, and views if APP_KEY is set
if [ -n "$APP_KEY" ]; then
    echo "Optimizing Laravel for production..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Execute database migrations if database is configured
if [ -n "$DATABASE_URL" ] || [ -n "$DB_HOST" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || echo "Migration skipped or database not reachable yet"
fi

echo "Starting Supervisord (PHP-FPM + Nginx)..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
