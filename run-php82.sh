#!/bin/bash
# Wrapper script to run PHP 8.2 commands for EduSphere
# This ensures we use PHP 8.2 without affecting system default

# Check if PHP 8.2 is installed
if ! command -v php8.2 &> /dev/null; then
    echo "PHP 8.2 is not installed."
    echo "Run: sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-redis"
    exit 1
fi

# Run command with PHP 8.2
php8.2 "$@"

