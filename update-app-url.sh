#!/bin/bash

echo "=== Update APP_URL Configuration ==="
echo ""

# Get current IP
echo "Detecting server IP addresses..."
echo ""
echo "Available IP addresses:"
hostname -I 2>/dev/null || ipconfig getifaddr en0 2>/dev/null || echo "Could not detect IP"
echo ""

# Prompt for APP_URL
read -p "Enter the URL you want to use (e.g., http://10.0.3.42 or https://yourdomain.com): " NEW_URL

if [ -z "$NEW_URL" ]; then
    echo "Error: URL cannot be empty"
    exit 1
fi

# Update .env file
if [ -f .env ]; then
    echo "Updating .env file..."
    
    # Backup .env
    cp .env .env.backup
    
    # Update APP_URL
    sed -i.bak "s|^APP_URL=.*|APP_URL=$NEW_URL|" .env
    
    echo "APP_URL updated to: $NEW_URL"
    echo "Backup saved to: .env.backup"
else
    echo "Error: .env file not found"
    exit 1
fi

echo ""
echo "Clearing config cache in Docker..."

# Detect docker command
DOCKER="docker"
if ! $DOCKER info >/dev/null 2>&1; then
    if sudo docker info >/dev/null 2>&1; then
        DOCKER="sudo docker"
    fi
fi

# Clear and rebuild cache
$DOCKER exec ebbl_app php artisan config:clear
$DOCKER exec ebbl_app php artisan config:cache

echo ""
echo "Restarting container..."
$DOCKER compose restart

echo ""
echo "=== Update completed! ==="
echo "New APP_URL: $NEW_URL"
echo ""
echo "Please test your application at: $NEW_URL/admin"
