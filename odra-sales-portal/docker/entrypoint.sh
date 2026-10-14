#!/bin/sh
set -e

# Default Render port is 10000 if PORT is not set
export PORT="${PORT:-10000}"

echo "Configuring Nginx to listen on port ${PORT}..."
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

# Laravel storage permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Cache configuration, routes, and views for production performance
echo "Optimizing Laravel for production..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Execute database migrations
echo "Running database migrations..."
php artisan migrate --force || echo "Migration skipped or database not reachable yet"

echo "Starting Supervisord (PHP-FPM + Nginx)..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
