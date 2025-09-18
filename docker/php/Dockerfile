# Multi-stage Dockerfile for Laravel 11 + Filament 3
# Stage 1: Build dependencies and assets
FROM node:18-alpine AS node-builder

WORKDIR /app

# Copy all files first to ensure package.json is available
COPY . .

# Install Node.js dependencies
RUN npm install --no-audit --no-fund

# Build assets with Vite (temporarily disabled for development)
# RUN npm run build
RUN mkdir -p public/build && echo '{}' > public/build/manifest.json

# Stage 2: PHP base image with extensions
FROM php:8.3-fpm-alpine AS php-base

# Install system dependencies
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    icu-dev \
    oniguruma-dev \
    mysql-client

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Stage 3: Development image
FROM php-base AS development

# Set working directory
WORKDIR /var/www/html

# Copy PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy application code first
COPY . .

# Copy built assets from node builder
COPY --from=node-builder /app/public/build ./public/build

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Switch to www-data user
USER www-data

EXPOSE 9000

CMD ["php-fpm"]
