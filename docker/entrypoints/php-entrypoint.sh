#!/bin/bash
set -e

# Function to wait for database
wait_for_db() {
    echo "Waiting for database connection..."
    while ! php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null; do
        echo "Database is unavailable - sleeping"
        sleep 2
    done
    echo "Database is ready!"
}

# Create necessary directories
mkdir -p /var/log
mkdir -p /tmp/opcache

# Set proper permissions
if [ "$1" = 'php-fpm' ]; then
    # Check if we're in a production environment
    if [ "${APP_ENV:-production}" = "production" ]; then
        echo "Production environment detected"
        
        # Cache configuration and routes for better performance
        if [ -f /var/www/html/artisan ]; then
            echo "Caching Laravel configurations..."
            php artisan config:cache --no-interaction
            php artisan route:cache --no-interaction
            php artisan view:cache --no-interaction
            php artisan event:cache --no-interaction
        fi
    else
        echo "Development environment detected"
        
        # Clear cache in development
        if [ -f /var/www/html/artisan ]; then
            echo "Clearing Laravel caches..."
            php artisan config:clear --no-interaction || true
            php artisan route:clear --no-interaction || true
            php artisan view:clear --no-interaction || true
            php artisan cache:clear --no-interaction || true
        fi
    fi

    # Wait for database and run migrations
    if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
        wait_for_db
        
        # Run migrations
        if [ -f /var/www/html/artisan ]; then
            echo "Running database migrations..."
            php artisan migrate --force --no-interaction
            
            # Run seeders if in development
            if [ "${APP_ENV:-production}" != "production" ]; then
                echo "Running database seeders..."
                php artisan db:seed --force --no-interaction || true
            fi
        fi
    fi

    # Generate application key if not set
    if [ -f /var/www/html/artisan ] && [ -z "${APP_KEY}" ]; then
        echo "Generating application key..."
        php artisan key:generate --no-interaction
    fi

    # Create storage link
    if [ -f /var/www/html/artisan ]; then
        echo "Creating storage link..."
        php artisan storage:link --no-interaction || true
    fi

    # Set proper permissions for Laravel
    echo "Setting proper permissions..."
    chmod -R 775 /var/www/html/storage || true
    chmod -R 775 /var/www/html/bootstrap/cache || true
fi

# Execute the original command
exec "$@"