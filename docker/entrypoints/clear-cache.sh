#!/bin/sh

# Script untuk clear cache Laravel di Docker
# Jalankan script ini setelah deployment atau saat ada masalah routing

echo "Clearing Laravel caches..."

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Clear Filament caches
php artisan filament:clear-cached-components

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Recreate storage link
php artisan storage:link

echo "Cache cleared and optimized successfully!"
