#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

echo "🚀 Starting deployment..."

# 1. Build and start containers in detached mode
echo "Building and starting Docker containers..."
docker-compose up -d --build

# 2. Install Composer dependencies
echo "Installing Composer dependencies..."
docker-compose exec -T app composer install --no-interaction --optimize-autoloader --no-dev

# 3. Install NPM dependencies and build assets
echo "Installing NPM dependencies and building assets..."
docker-compose exec -T app npm install
docker-compose exec -T app npm run build

# 4. Run database migrations
echo "Running database migrations..."
docker-compose exec -T app php artisan migrate --force

# 5. Optimize Laravel application
echo "Optimizing Laravel..."
docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache
docker-compose exec -T app php artisan event:cache

# 6. Restart PHP-FPM to apply Opcache changes
echo "Restarting PHP-FPM to clear Opcache..."
docker-compose restart app

echo "✅ Deployment finished successfully!"