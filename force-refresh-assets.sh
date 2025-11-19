#!/bin/bash

echo "==================================="
echo "Force Refresh Assets (Production)"
echo "==================================="
echo ""

# Check if running in Docker
if docker ps | grep -q ebbl_app; then
    echo "Running in Docker mode..."
    DOCKER_PREFIX="docker exec -it ebbl_app"
else
    echo "Running in local mode..."
    DOCKER_PREFIX=""
fi

echo ""
echo "1. Clearing all caches..."
$DOCKER_PREFIX php artisan optimize:clear

echo ""
echo "2. Clearing Filament cache..."
$DOCKER_PREFIX php artisan filament:clear-cached-components

echo ""
echo "3. Touching JavaScript file to update timestamp..."
touch public/js/fix-livewire-redirect.js

echo ""
echo "4. Rebuilding config cache..."
$DOCKER_PREFIX php artisan config:cache

echo ""
echo "5. Rebuilding view cache..."
$DOCKER_PREFIX php artisan view:cache

if docker ps | grep -q ebbl_app; then
    echo ""
    echo "6. Restarting Docker container..."
    docker compose restart
fi

echo ""
echo "==================================="
echo "✓ Assets refreshed successfully!"
echo "==================================="
echo ""
echo "IMPORTANT: Clear browser cache!"
echo "- Chrome/Edge: Ctrl+Shift+Delete or Ctrl+Shift+R"
echo "- Firefox: Ctrl+Shift+Delete or Ctrl+F5"
echo "- Safari: Cmd+Option+E"
echo ""
echo "Or open in Incognito/Private mode to test."
