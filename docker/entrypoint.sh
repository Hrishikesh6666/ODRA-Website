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

# If DATABASE_URL is provided, map to DB_URL and DB_CONNECTION
if [ -n "$DATABASE_URL" ]; then
    export DB_URL="$DATABASE_URL"
    if echo "$DATABASE_URL" | grep -q "postgres"; then
        export DB_CONNECTION="${DB_CONNECTION:-pgsql}"
    fi
fi

# Clear any stale cached configuration
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Execute database migrations and seeders automatically
if [ -n "$DATABASE_URL" ] || [ -n "$DB_URL" ] || [ -n "$DB_HOST" ]; then
    echo "Running automatic database migrations..."
    php artisan migrate --force || echo "Migration notice: check database connection"

    echo "Running automatic database seeding..."
    php artisan db:seed --force || echo "Seeding notice: database already seeded or skipped"
fi

# Cache configuration, routes, and views if APP_KEY is set
if [ -n "$APP_KEY" ]; then
    echo "Optimizing Laravel for production..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "Starting Supervisord (PHP-FPM + Nginx)..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
