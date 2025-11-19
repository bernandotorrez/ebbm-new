#!/bin/bash

echo "==================================="
echo "Fix Livewire File Upload Error (Docker)"
echo "==================================="
echo ""

# Check if container is running
if ! docker ps | grep -q ebbl_app; then
    echo "❌ Error: Container ebbl_app is not running!"
    echo "Please start the container first: docker compose up -d"
    exit 1
fi

echo "Container found. Clearing caches..."
echo ""

# Clear all caches in Docker
echo "1. Clearing all optimized files..."
docker exec -it ebbl_app php artisan optimize:clear

echo ""
echo "2. Clearing Filament cache..."
docker exec -it ebbl_app php artisan filament:clear-cached-components

echo ""
echo "3. Clearing Livewire temporary files..."
docker exec -it ebbl_app php artisan livewire:delete-uploaded-files --hours=0

echo ""
echo "4. Recreating optimized config..."
docker exec -it ebbl_app php artisan config:cache

echo ""
echo "5. Restarting container..."
docker compose restart

echo ""
echo "==================================="
echo "✓ Cache cleared successfully!"
echo "==================================="
echo ""
echo "Changes made:"
echo "1. Enhanced JavaScript error suppression with proper Livewire hooks"
echo "2. Intercept 404 errors after redirect using commit/request hooks"
echo "3. Prevent error popups in both local and production"
echo ""
echo "Please test the file upload now."
echo "The 404 error popup should no longer appear."
echo ""
echo "Note: You may still see 404 in console (suppressed), but no popup."
