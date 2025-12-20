# Virtual Host Setup Guide

## Quick Setup

### Step 1: Add to /etc/hosts

```bash
sudo nano /etc/hosts
```

Add these lines:
```
127.0.0.1    edusphere.local
127.0.0.1    www.edusphere.local
127.0.0.1    demo.edusphere.local
127.0.0.1    *.edusphere.local
```

### Step 2: Choose Your Web Server

## Option A: Nginx (Recommended)

### Install Nginx (if not installed)
```bash
sudo apt update
sudo apt install nginx
```

### Copy Configuration
```bash
sudo cp /var/projects/EduSphere/nginx.conf /etc/nginx/sites-available/edusphere
```

### Enable Site
```bash
sudo ln -s /etc/nginx/sites-available/edusphere /etc/nginx/sites-enabled/
```

### Test Configuration
```bash
sudo nginx -t
```

### Restart Nginx
```bash
sudo systemctl restart nginx
```

### Access Your Site
- Main: http://edusphere.local
- Login: http://edusphere.local/login
- Demo School: http://demo.edusphere.local

## Option B: Apache

### Install Apache (if not installed)
```bash
sudo apt update
sudo apt install apache2
sudo apt install libapache2-mod-php8.2
```

### Enable Required Modules
```bash
sudo a2enmod rewrite
sudo a2enmod proxy_fcgi
sudo a2enmod setenvif
sudo a2enmod headers
```

### Copy Configuration
```bash
sudo cp /var/projects/EduSphere/apache.conf /etc/apache2/sites-available/edusphere.conf
```

### Enable Site
```bash
sudo a2ensite edusphere.conf
sudo a2dissite 000-default.conf  # Disable default site (optional)
```

### Test Configuration
```bash
sudo apache2ctl configtest
```

### Restart Apache
```bash
sudo systemctl restart apache2
```

### Access Your Site
- Main: http://edusphere.local
- Login: http://edusphere.local/login
- Demo School: http://demo.edusphere.local

## Verify Setup

### Check if site is accessible
```bash
curl -H "Host: edusphere.local" http://localhost
```

### Check PHP is working
```bash
php8.2 -v
```

### Check PHP-FPM is running
```bash
sudo systemctl status php8.2-fpm
```

## Troubleshooting

### Issue: 403 Forbidden
**Solution**: Check file permissions
```bash
sudo chown -R www-data:www-data /var/projects/EduSphere/storage
sudo chown -R www-data:www-data /var/projects/EduSphere/bootstrap/cache
sudo chmod -R 775 /var/projects/EduSphere/storage
sudo chmod -R 775 /var/projects/EduSphere/bootstrap/cache
```

### Issue: 502 Bad Gateway
**Solution**: Check PHP-FPM is running
```bash
sudo systemctl start php8.2-fpm
sudo systemctl enable php8.2-fpm
```

### Issue: Site not found
**Solution**: 
1. Verify /etc/hosts entry
2. Check DNS: `ping edusphere.local`
3. Verify virtual host is enabled
4. Check web server is running: `sudo systemctl status nginx` or `sudo systemctl status apache2`

### Issue: CSS/JS not loading
**Solution**: Make sure assets are built
```bash
cd /var/projects/EduSphere
npm run build
```

## Multi-Tenant Subdomains

The virtual host is configured to support wildcard subdomains:
- `demo.edusphere.local` → Demo School
- `school1.edusphere.local` → School 1
- `school2.edusphere.local` → School 2

All subdomains will work automatically!

## SSL/HTTPS (Optional)

To add SSL with Let's Encrypt:

```bash
sudo apt install certbot python3-certbot-nginx  # For Nginx
# OR
sudo apt install certbot python3-certbot-apache  # For Apache

sudo certbot --nginx -d edusphere.local -d www.edusphere.local
# OR
sudo certbot --apache -d edusphere.local -d www.edusphere.local
```

## Quick Setup Script

Run this script to set up everything automatically:

```bash
#!/bin/bash
# Add to /etc/hosts
echo "127.0.0.1    edusphere.local" | sudo tee -a /etc/hosts
echo "127.0.0.1    www.edusphere.local" | sudo tee -a /etc/hosts
echo "127.0.0.1    demo.edusphere.local" | sudo tee -a /etc/hosts

# Copy Nginx config
sudo cp /var/projects/EduSphere/nginx.conf /etc/nginx/sites-available/edusphere
sudo ln -sf /etc/nginx/sites-available/edusphere /etc/nginx/sites-enabled/

# Test and restart
sudo nginx -t && sudo systemctl restart nginx

echo "Virtual host setup complete!"
echo "Access: http://edusphere.local"
```

