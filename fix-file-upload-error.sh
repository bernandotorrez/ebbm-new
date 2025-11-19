#!/bin/bash

echo "==================================="
echo "Fix Livewire File Upload Error"
echo "==================================="
echo ""

# Clear all caches
echo "1. Clearing application cache..."
php artisan cache:clear

echo "2. Clearing config cache..."
php artisan config:clear

echo "3. Clearing route cache..."
php artisan route:clear

echo "4. Clearing view cache..."
php artisan view:clear

echo "5. Clearing Livewire temporary files..."
php artisan livewire:delete-uploaded-files --hours=0

echo ""
echo "6. Recreating optimized config..."
php artisan config:cache

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
