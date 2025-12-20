#!/bin/bash

echo "=== EduSphere Virtual Host Setup ==="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "Please run with sudo"
    exit 1
fi

# Detect web server
if command -v nginx &> /dev/null; then
    SERVER="nginx"
elif command -v apache2 &> /dev/null; then
    SERVER="apache2"
else
    echo "Neither Nginx nor Apache found. Please install one first."
    exit 1
fi

echo "Detected web server: $SERVER"
echo ""

# Add to /etc/hosts
echo "Adding entries to /etc/hosts..."
if ! grep -q "edusphere.local" /etc/hosts; then
    echo "127.0.0.1    edusphere.local" >> /etc/hosts
    echo "127.0.0.1    www.edusphere.local" >> /etc/hosts
    echo "127.0.0.1    demo.edusphere.local" >> /etc/hosts
    echo "✓ Added to /etc/hosts"
else
    echo "✓ Already in /etc/hosts"
fi

# Setup based on server
if [ "$SERVER" = "nginx" ]; then
    echo ""
    echo "Setting up Nginx..."
    cp /var/projects/EduSphere/nginx.conf /etc/nginx/sites-available/edusphere
    ln -sf /etc/nginx/sites-available/edusphere /etc/nginx/sites-enabled/
    
    echo "Testing Nginx configuration..."
    if nginx -t; then
        systemctl restart nginx
        echo "✓ Nginx configured and restarted"
    else
        echo "✗ Nginx configuration error"
        exit 1
    fi
elif [ "$SERVER" = "apache2" ]; then
    echo ""
    echo "Setting up Apache..."
    a2enmod rewrite proxy_fcgi setenvif headers
    cp /var/projects/EduSphere/apache.conf /etc/apache2/sites-available/edusphere.conf
    a2ensite edusphere.conf
    
    echo "Testing Apache configuration..."
    if apache2ctl configtest; then
        systemctl restart apache2
        echo "✓ Apache configured and restarted"
    else
        echo "✗ Apache configuration error"
        exit 1
    fi
fi

# Set permissions
echo ""
echo "Setting permissions..."
chown -R www-data:www-data /var/projects/EduSphere/storage
chown -R www-data:www-data /var/projects/EduSphere/bootstrap/cache
chmod -R 775 /var/projects/EduSphere/storage
chmod -R 775 /var/projects/EduSphere/bootstrap/cache
echo "✓ Permissions set"

echo ""
echo "=== Setup Complete ==="
echo ""
echo "Access your site at:"
echo "  - http://edusphere.local"
echo "  - http://edusphere.local/login"
echo "  - http://demo.edusphere.local (for demo school)"
echo ""
