# Stage 1: Base image with PHP extensions
FROM php:8.2-fpm-alpine AS base
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libxpm-dev \
    icu-dev \
    oniguruma-dev

# Install PHP extensions required by Laravel & Filament
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    gd \
    intl \
    opcache \
    pdo_mysql \
    zip \
    exif \
    bcmath

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set user for security
RUN addgroup -g 1000 www && adduser -u 1000 -G www -s /bin/sh -D www
USER www

# Copy existing application directory contents
COPY --chown=www:www ./src .

# --------------------------------------------------------------------

# Stage 2: Build stage for assets
FROM node:18-alpine AS build
WORKDIR /var/www/html
COPY --from=base /var/www/html .
RUN npm install && npm run build

# --------------------------------------------------------------------

# Stage 3: Production image
FROM base AS production
WORKDIR /var/www/html

# Install Composer dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Copy built assets from the build stage
COPY --from=build /var/www/html/public/build /var/www/html/public/build

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]