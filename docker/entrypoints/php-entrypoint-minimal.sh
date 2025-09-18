#!/bin/bash
set -e

echo "Starting PHP-FPM directly..."

# Create necessary directories
mkdir -p /var/log
mkdir -p /tmp/opcache

# Start PHP-FPM in foreground
exec php-fpm -F