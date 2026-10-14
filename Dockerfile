# Multi-stage production Dockerfile for Laravel on Render
FROM php:8.3-fpm-alpine

# Install system dependencies & Nginx & Supervisor & envsubst
RUN apk add --no-cache \
    nginx \
    supervisor \
    gettext \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev \
    icu-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        zip \
        bcmath \
        gd \
        intl \
        opcache

# Copy Composer binary from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application source code from odra-sales-portal directory
COPY odra-sales-portal/ .

# Install PHP dependencies for production
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Configure Nginx & Supervisord
RUN mkdir -p /etc/nginx/templates /run/nginx /var/log/supervisor
COPY odra-sales-portal/docker/nginx.conf /etc/nginx/templates/default.conf.template
COPY odra-sales-portal/docker/supervisord.conf /etc/supervisord.conf
COPY odra-sales-portal/docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set directory permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Render uses port 10000 by default (dynamic via $PORT)
EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
