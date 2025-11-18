#!/bin/bash

echo "=== Testing Livewire Routes ==="
echo ""

# Detect docker command
DOCKER="docker"
if ! $DOCKER info >/dev/null 2>&1; then
    if sudo docker info >/dev/null 2>&1; then
        DOCKER="sudo docker"
    else
        echo "Error: Docker is not running"
        exit 1
    fi
fi

echo "1. Checking Livewire routes..."
$DOCKER exec ebbl_app php artisan route:list --path=livewire

echo ""
echo "2. Checking Livewire config..."
$DOCKER exec ebbl_app php artisan config:show livewire

echo ""
echo "3. Checking storage permissions..."
$DOCKER exec ebbl_app ls -la storage/app/

echo ""
echo "4. Checking livewire-tmp directory..."
$DOCKER exec ebbl_app sh -c "mkdir -p storage/app/livewire-tmp && chmod -R 775 storage/app/livewire-tmp && chown -R www-data:www-data storage/app/livewire-tmp"

echo ""
echo "5. Testing file upload endpoint..."
curl -I http://localhost/livewire/upload-file

echo ""
echo "=== Test completed! ==="
