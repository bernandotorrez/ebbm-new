#!/bin/bash

# Initialize Laravel application

echo "Initializing Laravel application..."

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install

# Install Node.js dependencies
echo "Installing Node.js dependencies..."
npm install

# Generate application key
echo "Generating application key..."
php artisan key:generate

# Run database migrations
echo "Running database migrations..."
php artisan migrate

echo "Laravel application initialized successfully!"