# Use PHP 8.3.17 FPM Alpine for smaller image size and better performance
FROM php:8.3.17-fpm-alpine

# Set environment variables
ENV APP_ENV=production
ENV APP_DEBUG=false

# Install system dependencies and PHP extensions for Laravel and performance
RUN apk add --no-cache \
    # System dependencies
    bash \
    curl \
    freetype-dev \
    git \
    icu-dev \
    jpeg-dev \
    libpng-dev \
    libzip-dev \
    mysql-client \
    oniguruma-dev \
    zip \
    unzip \
    supervisor \
    # Build dependencies (will be removed later)
    autoconf \
    g++ \
    gcc \
    libc-dev \
    make \
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
    # Install Redis extension for caching
    && pecl install redis \
    && docker-php-ext-enable redis \
    # Clean up build dependencies
    && apk del autoconf g++ gcc libc-dev make \
    && rm -rf /var/cache/apk/* /tmp/* /var/tmp/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy PHP configuration files
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php-fpm-pool.conf /usr/local/etc/php-fpm.d/www.conf

# Create app user and group
RUN addgroup -g 1000 -S app && \
    adduser -u 1000 -S app -G app

# Set working directory
WORKDIR /var/www/html

# Copy application files with proper ownership
COPY --chown=app:app . /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy entrypoint script
COPY docker/entrypoints/php-entrypoint-minimal.sh /usr/local/bin/php-entrypoint.sh
RUN chmod +x /usr/local/bin/php-entrypoint.sh

# Switch to app user
USER app

# Expose port 9000
EXPOSE 9000

# Use custom entrypoint
ENTRYPOINT ["/usr/local/bin/php-entrypoint.sh"]

# Start PHP-FPM
CMD ["php-fpm", "-F"]