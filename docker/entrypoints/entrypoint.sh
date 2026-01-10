#!/bin/sh

# Exit on error
set -e

# Create storage directories if they don't exist
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/livewire-tmp
mkdir -p storage/app/public

# Set proper permissions and ownership
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Ensure livewire-tmp has proper permissions
chmod -R 775 storage/app/livewire-tmp
chown -R www-data:www-data storage/app/livewire-tmp

# Create storage symbolic link
echo "Creating storage symbolic link..."
php artisan storage:link || echo "Storage link already exists or failed, continuing..."

# Publish Livewire assets
echo "Publishing Livewire assets..."
php artisan livewire:publish --assets --force || echo "Livewire assets publish failed, continuing..."

# Publish Filament assets
echo "Publishing Filament assets..."
php artisan filament:assets || echo "Filament assets publish failed, continuing..."

# Optimize application
echo "Optimizing application..."
php artisan optimize || echo "Optimization failed, continuing..."

# Ensure PHP-FPM directory exists
mkdir -p /var/run/php-fpm

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
max_attempts=30
attempt=0
until php artisan db:show 2>/dev/null || [ $attempt -eq $max_attempts ]; do
  attempt=$((attempt + 1))
  echo "Waiting for database connection... (attempt $attempt/$max_attempts)"
  sleep 2
done

if [ $attempt -eq $max_attempts ]; then
  echo "Warning: Could not connect to database after $max_attempts attempts. Continuing anyway..."
else
  echo "MySQL is ready!"
fi

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
else
    echo "No .env file found. Skipping artisan key generation."
fi

# Execute the main command
exec "$@"