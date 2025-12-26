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

# Ensure APP_URL is set correctly (force HTTPS in production)
if [ -z "$APP_URL" ] || [[ ! "$APP_URL" =~ ^https:// ]]; then
    echo "Warning: APP_URL should be set to HTTPS URL in Railway environment variables"
fi

echo "Storage directories created and permissions set."
