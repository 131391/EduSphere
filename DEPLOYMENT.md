# Deployment Guide

## Server Requirements

- PHP 8.2+ with extensions: mysql, xml, mbstring, curl, zip, gd, redis, bcmath
- MySQL 8.0+ or MariaDB 10.5+
- Redis 6.0+
- Nginx or Apache
- SSL certificate

## Pre-Deployment

```bash
# 1. Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# 2. Environment setup
cp .env.example .env
# Edit .env with production values

# 3. Generate key
php artisan key:generate

# 4. Run migrations
php artisan migrate --force

# 5. Install Passport
php artisan passport:install

# 6. Create storage link
php artisan storage:link
```

## Production Optimization

```bash
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer dump-autoload --optimize --classmap-authoritative
```

## Queue Setup

### Install Horizon

```bash
php artisan horizon:install
php artisan horizon:publish
```

### Supervisor Configuration

Create `/etc/supervisor/conf.d/horizon.conf`:

```ini
[program:horizon]
process_name=%(program_name)s
command=php /var/www/edusphere/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/edusphere/storage/logs/horizon.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start horizon
```

## Nginx Configuration

```nginx
server {
    listen 80;
    server_name edusphere.com *.edusphere.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name edusphere.com *.edusphere.com;

    ssl_certificate /etc/letsencrypt/live/edusphere.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/edusphere.com/privkey.pem;

    root /var/www/edusphere/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Backup Strategy

### Automated Backups

Configure in `config/backup.php`:

```php
'disks' => ['s3'], // or 'local'
```

Schedule in `app/Console/Kernel.php`:

```php
$schedule->command('backup:run')->daily()->at('02:00');
```

### Manual Backup

```bash
php artisan backup:run
```

## Monitoring

- Laravel Horizon dashboard: `/horizon`
- Error tracking: Configure Sentry or similar
- Uptime monitoring: UptimeRobot, Pingdom

## Security Checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] SSL certificate installed
- [ ] Firewall configured
- [ ] File permissions set (755 directories, 644 files)
- [ ] `.env` file secured (600 permissions)
- [ ] Regular security updates

## Rollback Procedure

```bash
# Revert code
git reset --hard HEAD~1

# Restore database
mysql -u user -p database < backup.sql

# Clear cache
php artisan optimize:clear
```

