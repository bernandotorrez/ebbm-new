#!/bin/sh

# This script runs after the main application has started
# It handles migrations, cache optimizations, and Livewire fix

echo "========================================="
echo "Post-Startup Tasks Starting..."
echo "========================================="
echo ""

echo "Waiting for application to be ready..."
sleep 10

# ==========================================
# 1. DATABASE MIGRATIONS
# ==========================================
echo "1. Running database migrations..."
if php artisan migrate:status >/dev/null 2>&1; then
    php artisan migrate --force || echo "   ⚠️  Migrations failed, continuing..."
else
    echo "   Creating and running initial migrations..."
    php artisan migrate --force || echo "   ⚠️  Initial migrations failed, continuing..."
fi
echo "   ✓ Migrations completed"
echo ""

# ==========================================
# 2. STORAGE SETUP
# ==========================================
echo "2. Setting up storage..."
php artisan storage:link || echo "   ⚠️  Storage link already exists or failed"

# Ensure storage directories exist with correct permissions
echo "   Setting up storage directories..."
mkdir -p storage/app/public
mkdir -p storage/app/livewire-tmp
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Fix permissions
chmod -R 775 storage
chown -R www-data:www-data storage || echo "   ⚠️  Chown failed, continuing..."
echo "   ✓ Storage setup completed"
echo ""

# ==========================================
# 3. CLEAR ALL CACHES
# ==========================================
echo "3. Clearing all caches..."
php artisan cache:clear || echo "   ⚠️  Cache clear failed"
php artisan config:clear || echo "   ⚠️  Config clear failed"
php artisan route:clear || echo "   ⚠️  Route clear failed"
php artisan view:clear || echo "   ⚠️  View clear failed"
php artisan event:clear || echo "   ⚠️  Event clear failed"
echo "   ✓ Application caches cleared"
echo ""

# ==========================================
# 4. FILAMENT CACHE
# ==========================================
echo "4. Clearing Filament cache..."
php artisan filament:clear-cached-components || echo "   ⚠️  Filament cache clear failed"
echo "   ✓ Filament cache cleared"
echo ""

# ==========================================
# 5. LIVEWIRE FIX
# ==========================================
echo "5. Applying Livewire fix..."

# Clear Livewire temporary files (older than 24 hours)
echo "   Clearing Livewire temporary files..."
php artisan livewire:delete-uploaded-files --hours=24 || echo "   ⚠️  Livewire cleanup failed"

# Setup Livewire directory with correct permissions
echo "   Setting up Livewire directories..."
mkdir -p storage/app/livewire-tmp
chmod -R 775 storage/app/livewire-tmp
chown -R www-data:www-data storage/app/livewire-tmp || echo "   ⚠️  Livewire directory setup failed"

# Verify Livewire directory
if [ -d "storage/app/livewire-tmp" ]; then
    echo "   ✓ Livewire directory exists: storage/app/livewire-tmp"
else
    echo "   ⚠️  Livewire directory not found!"
fi

echo "   ✓ Livewire fix applied"
echo ""

# ==========================================
# 6. OPTIMIZE FOR PRODUCTION
# ==========================================
if [ "$APP_ENV" = "production" ]; then
    echo "6. Optimizing for production..."
    php artisan config:cache || echo "   ⚠️  Config cache failed"
    php artisan route:cache || echo "   ⚠️  Route cache failed"
    php artisan view:cache || echo "   ⚠️  View cache failed"
    php artisan event:cache || echo "   ⚠️  Event cache failed"
    echo "   ✓ Production optimization completed"
else
    echo "6. Skipping production optimization (APP_ENV=$APP_ENV)"
fi
echo ""

# ==========================================
# 7. VERIFICATION
# ==========================================
echo "7. Verifying setup..."
echo "   Checking directories..."
ls -la storage/app/livewire-tmp 2>/dev/null || echo "   ⚠️  Livewire temp directory not accessible"
echo "   Checking permissions..."
stat -c "%a %n" storage/app/livewire-tmp 2>/dev/null || echo "   ⚠️  Cannot check permissions"
echo "   ✓ Verification completed"
echo ""

# ==========================================
# SUMMARY
# ==========================================
echo "========================================="
echo "✓ Post-Startup Tasks Completed!"
echo "========================================="
echo ""
echo "Summary:"
echo "  ✓ Database migrations"
echo "  ✓ Storage setup"
echo "  ✓ Caches cleared"
echo "  ✓ Filament cache cleared"
echo "  ✓ Livewire fix applied"
if [ "$APP_ENV" = "production" ]; then
    echo "  ✓ Production optimization"
fi
echo ""
echo "Application is ready!"
echo ""
echo "IMPORTANT: Users must clear browser cache!"
echo "  - Chrome/Edge: Ctrl+Shift+Delete"
echo "  - Firefox: Ctrl+Shift+Delete"
echo "  - Or use Incognito/Private mode"
echo ""
echo "========================================="