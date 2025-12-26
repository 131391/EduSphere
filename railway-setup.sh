#!/bin/bash
set -e

echo "Setting up Laravel storage directories..."

# Create storage directories if they don't exist
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/testing
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# Set proper permissions (Railway runs as root, so permissions should work)
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Create storage link if it doesn't exist
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

# Clear and cache config
php artisan config:clear || true
php artisan cache:clear || true

echo "Storage directories created and permissions set."
