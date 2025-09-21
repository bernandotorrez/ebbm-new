# Multi-stage Dockerfile for Laravel 11 + Filament 3

# Node.js Builder Stage for Vite/Tailwind Assets
FROM node:18-alpine AS node-builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci

# Copy Vite config and resources
COPY vite.config.js ./
COPY resources/ resources/

# Build assets
RUN npm run build

# PHP Stage
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    oniguruma-dev \
    openssl-dev \
    supervisor \
    nginx \
    zlib-dev \
    libzip-dev \
    freetype-dev \
    jpeg-dev \
    autoconf \
    build-base \
    icu-dev \
    libintl \
    && docker-php-ext-install \
    pdo_mysql \
    gd \
    mysqli \
    opcache \
    exif \
    pcntl \
    zip \
    intl

# Create directories for sockets and logs with proper permissions
RUN mkdir -p /var/run/php-fpm \
    && touch /var/run/php-fpm.sock \
    && chown www-data:www-data /var/run/php-fpm.sock

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy compiled assets from node-builder stage
COPY --from=node-builder /app/public/build/ public/build/

# Install Composer dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-scripts \
    && composer clear-cache

# Copy configuration files
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoints/entrypoint.sh /entrypoint.sh
COPY docker/entrypoints/post-startup.sh /post-startup.sh

# Set permissions
RUN chmod +x /entrypoint.sh \
    && chmod +x /post-startup.sh \
    && chown -R www-data:www-data /var/www/html \
    && mkdir -p storage bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && mkdir -p /var/log/supervisor \
    && touch /var/log/supervisord.log \
    && chown -R www-data:www-data /var/log/supervisor

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Entry point
ENTRYPOINT ["/entrypoint.sh"]

# Default command
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]