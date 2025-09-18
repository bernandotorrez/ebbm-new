#!/bin/bash
set -e

echo "Starting PHP container setup..."

# Create necessary directories
mkdir -p /var/log
mkdir -p /tmp/opcache

# Set proper permissions for Laravel directories (only if not volume-mounted)
echo "Setting proper permissions..."
if [ ! -L "/var/www/html/storage" ]; then
    chmod -R 775 /var/www/html/storage 2>/dev/null || echo "Warning: Could not set permissions on storage directory"
fi
if [ ! -L "/var/www/html/bootstrap/cache" ]; then
    chmod -R 775 /var/www/html/bootstrap/cache 2>/dev/null || echo "Warning: Could not set permissions on bootstrap/cache directory"
fi

# Generate application key if not set
if [ -f /var/www/html/artisan ] && [ -z "${APP_KEY}" ]; then
    echo "Generating application key..."
    php artisan key:generate --no-interaction || true
fi

echo "Setup completed. Starting PHP-FPM..."
# Execute the original command
exec "$@"