#!/bin/sh

# Exit on error
set -e

# Create storage directories if they don't exist
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Set proper permissions and ownership
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Ensure PHP-FPM directory exists
mkdir -p /var/run/php-fpm

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! nc -z mysql 3306; do
  sleep 1
done
echo "MySQL is ready!"

# Wait for Redis to be ready
echo "Waiting for Redis to be ready..."
while ! nc -z redis 6379; do
  sleep 1
done
echo "Redis is ready!"

# Check if .env file exists
if [ -f ".env" ]; then
    echo "Checking for APP_KEY..."
    
    # Check if APP_KEY is empty or not properly set
    APP_KEY=$(grep '^APP_KEY=' .env | cut -d '=' -f2-)
    
    if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "null" ] || [ "$APP_KEY" = "''" ] || [ "$APP_KEY" = '""' ]; then
        echo "Generating application key..."
        php artisan key:generate --ansi
    else
        echo "APP_KEY already exists, skipping generation."
    fi
    
    echo "Running artisan optimizations..."
    
    # Clear caches
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
    
    echo "Artisan optimizations completed."
else
    echo "No .env file found. Skipping artisan optimizations."
fi

# Execute the main command
exec "$@"