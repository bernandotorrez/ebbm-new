# Multi-stage build for better caching and smaller final image
ARG PHP_VERSION=8.3.17

# ===============================================
# Base PHP Image with Extensions
# ===============================================
FROM php:${PHP_VERSION}-fpm-alpine AS php-base

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    # Runtime dependencies
    bash \
    curl \
    mysql-client \
    zip \
    unzip \
    # Image processing libraries
    freetype \
    libjpeg-turbo \
    libpng \
    libzip \
    icu \
    oniguruma \
    && apk add --no-cache --virtual .build-deps \
    # Build dependencies
    autoconf \
    g++ \
    gcc \
    libc-dev \
    make \
    pkgconf \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo \
        pdo_mysql \
        zip \
    # Install Redis extension
    && pecl install redis \
    && docker-php-ext-enable redis \
    # Cleanup
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/* /var/tmp/* /usr/share/man /usr/share/doc

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create app user and directories
RUN addgroup -g 1000 -S app \
    && adduser -u 1000 -S app -G app \
    && mkdir -p /var/www/html /var/log/php \
    && chown -R app:app /var/www/html /var/log/php

# ===============================================
# Dependencies Stage
# ===============================================
FROM php-base AS dependencies

# Copy composer files for better layer caching
COPY composer.json composer.lock /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Install PHP dependencies as app user
USER app
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# ===============================================
# Production Stage
# ===============================================
FROM php-base AS production

# Copy PHP configuration files
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf

# Set working directory
WORKDIR /var/www/html

# Copy vendor from dependencies stage
COPY --from=dependencies --chown=app:app /var/www/html/vendor ./vendor

# Copy application code
COPY --chown=app:app . .

# Generate optimized autoloader
USER app
RUN composer dump-autoload --optimize --no-dev --no-scripts

# Create entrypoint script
USER root
RUN cat > /usr/local/bin/php-entrypoint.sh << 'EOF'
#!/bin/bash
set -e

# Create necessary directories
mkdir -p /var/log/php /tmp/opcache
chown -R app:app /var/log/php /tmp/opcache

# Wait for dependencies if in development
if [ "${APP_ENV:-production}" != "production" ]; then
    echo "Development environment detected"
    
    # Wait for database
    until nc -z ${DB_HOST:-mysql} ${DB_PORT:-3306}; do
        echo "Waiting for database..."
        sleep 2
    done
    
    # Wait for Redis
    until nc -z ${REDIS_HOST:-redis} ${REDIS_PORT:-6379}; do
        echo "Waiting for Redis..."
        sleep 2
    done
    
    echo "Dependencies are ready!"
fi

# Execute as app user
exec su-exec app "$@"
EOF

# Install su-exec for better user switching (Alpine equivalent of gosu)
RUN apk add --no-cache su-exec \
    && chmod +x /usr/local/bin/php-entrypoint.sh

# Switch to app user
USER app

# Expose port 9000
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD nc -z localhost 9000 || exit 1

# Use custom entrypoint
ENTRYPOINT ["/usr/local/bin/php-entrypoint.sh"]

# Start PHP-FPM
CMD ["php-fpm", "-F"]