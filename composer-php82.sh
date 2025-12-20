#!/bin/bash
# Composer wrapper using PHP 8.2 for EduSphere project

# Check if PHP 8.2 is installed
if ! command -v php8.2 &> /dev/null; then
    echo "PHP 8.2 is not installed."
    echo "Installing PHP 8.2..."
    echo "Run: sudo add-apt-repository ppa:ondrej/php -y && sudo apt update && sudo apt install -y php8.2 php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-redis"
    exit 1
fi

# Check if Composer is installed
if ! command -v composer &> /dev/null && [ ! -f /usr/bin/composer ]; then
    echo "Composer is not installed."
    exit 1
fi

# Use PHP 8.2 to run Composer
php8.2 /usr/bin/composer "$@"

