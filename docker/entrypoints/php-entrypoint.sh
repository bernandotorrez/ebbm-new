#!/bin/bash
set -e

# Function to wait for database
wait_for_db() {
    echo "Waiting for database connection..."
    for i in {1..30}; do
        if php artisan tinker --execute="DB::connection()->getPdo();" >/dev/null 2>&1; then
            echo "Database is ready!"
            return 0
        fi
        echo "Database is unavailable - sleeping (attempt $i/30)"
        sleep 2
    done
    echo "Failed to connect to database after 30 attempts"
    return 1
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
        
        # Clear cache in development (only once)
        if [ -f /var/www/html/artisan ] && [ ! -f /tmp/.laravel-cache-cleared ]; then
            echo "Clearing Laravel caches..."
            php artisan config:clear --no-interaction || true
            php artisan route:clear --no-interaction || true
            php artisan view:clear --no-interaction || true
            php artisan cache:clear --no-interaction || true
            touch /tmp/.laravel-cache-cleared
        fi
    fi

    # Wait for database and run migrations (only if DB_CONNECTION is mysql)
    if [ "${DB_CONNECTION:-}" = "mysql" ]; then
        if wait_for_db; then
            # Run migrations (only once)
            if [ -f /var/www/html/artisan ] && [ ! -f /tmp/.laravel-migrated ]; then
                echo "Running database migrations..."
                php artisan migrate --force --no-interaction
                touch /tmp/.laravel-migrated
                
                # Run seeders if in development (only once)
                if [ "${APP_ENV:-production}" != "production" ] && [ ! -f /tmp/.laravel-seeded ]; then
                    echo "Running database seeders..."
                    php artisan db:seed --force --no-interaction || true
                    touch /tmp/.laravel-seeded
                fi
            fi
        else
            echo "Skipping migrations due to database connection failure"
        fi
    fi

    # Generate application key if not set (only once)
    if [ -f /var/www/html/artisan ] && [ -z "${APP_KEY}" ] && [ ! -f /tmp/.laravel-key-generated ]; then
        echo "Generating application key..."
        php artisan key:generate --no-interaction
        touch /tmp/.laravel-key-generated
    fi

    # Create storage link (only once)
    if [ -f /var/www/html/artisan ] && [ ! -f /tmp/.laravel-storage-linked ]; then
        echo "Creating storage link..."
        php artisan storage:link --no-interaction || true
        touch /tmp/.laravel-storage-linked
    fi

    # Set proper permissions for Laravel
    echo "Setting proper permissions..."
    chmod -R 775 /var/www/html/storage || true
    chmod -R 775 /var/www/html/bootstrap/cache || true
fi

echo "Starting PHP-FPM..."
# Execute the original command
exec "$@"