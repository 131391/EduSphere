# Deployment Guide for EduSphere

This guide covers deployment options for the EduSphere Laravel application.

## 🚀 Recommended Platforms

### 1. Railway (Recommended - Easiest)

**Why Railway:**
- ✅ One-click deployment from GitHub
- ✅ Automatic database provisioning
- ✅ Built-in Redis support
- ✅ Free tier available
- ✅ Simple configuration

**Steps:**
1. Go to [railway.app](https://railway.app)
2. Sign up with GitHub
3. Click "New Project" → "Deploy from GitHub repo"
4. Select your `EduSphere` repository
5. Railway will auto-detect Laravel and configure it
6. Add MySQL database service
7. Add Redis service (optional but recommended)
8. Set environment variables (see below)
9. Deploy!

**Environment Variables:**
```env
APP_NAME=EduSphere
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://web-production-df4f.up.railway.app
# Important: Use your actual Railway URL with https://

DB_CONNECTION=mysql
DB_HOST=${{MySQL.HOSTNAME}}
DB_PORT=${{MySQL.PORT}}
DB_DATABASE=${{MySQL.DATABASE}}
DB_USERNAME=${{MySQL.USERNAME}}
DB_PASSWORD=${{MySQL.PASSWORD}}

REDIS_HOST=${{Redis.HOSTNAME}}
REDIS_PASSWORD=${{Redis.PASSWORD}}
REDIS_PORT=${{Redis.PORT}}

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

**Post-Deployment:**
```bash
php artisan migrate --force
php artisan db:seed
php artisan storage:link
```

---

### 2. Render

**Why Render:**
- ✅ Free tier with PostgreSQL
- ✅ Automatic SSL
- ✅ Easy GitHub integration
- ✅ Good for small to medium apps

**Steps:**
1. Go to [render.com](https://render.com)
2. Sign up with GitHub
3. Click "New" → "Web Service"
4. Connect your GitHub repository
5. Configure:
   - **Name:** edusphere
   - **Environment:** PHP
   - **Build Command:** `composer install --no-dev --optimize-autoloader && npm install && npm run build && php artisan config:cache && php artisan route:cache && php artisan view:cache`
   - **Start Command:** `php artisan serve --host=0.0.0.0 --port=$PORT`
6. Add PostgreSQL database
7. Set environment variables
8. Deploy!

**Note:** Render uses PostgreSQL by default. Update your `.env`:
```env
DB_CONNECTION=pgsql
```

---

### 3. DigitalOcean App Platform

**Why DigitalOcean:**
- ✅ Managed Laravel hosting
- ✅ Auto-scaling
- ✅ Integrated database
- ✅ Good performance

**Steps:**
1. Go to [cloud.digitalocean.com](https://cloud.digitalocean.com)
2. Navigate to App Platform
3. Click "Create App" → "GitHub"
4. Select your repository
5. Configure:
   - **Type:** Web Service
   - **Build Command:** `composer install --no-dev --optimize-autoloader && npm install && npm run build`
   - **Run Command:** `php artisan serve --host=0.0.0.0 --port=$PORT`
6. Add managed database
7. Set environment variables
8. Deploy!

---

### 4. Laravel Forge

**Why Forge:**
- ✅ Specifically designed for Laravel
- ✅ One-click server provisioning
- ✅ Automatic deployments
- ✅ Best for production

**Steps:**
1. Go to [forge.laravel.com](https://forge.laravel.com)
2. Sign up and connect DigitalOcean/AWS/Linode
3. Create a new server
4. Create a new site
5. Connect your GitHub repository
6. Configure deployment script
7. Set environment variables
8. Deploy!

**Deployment Script (Forge):**
```bash
cd /home/forge/your-domain.com
git pull origin main
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

---

### 5. Heroku

**Why Heroku:**
- ✅ Easy deployment
- ✅ Add-ons marketplace
- ✅ Good documentation

**Steps:**
1. Install Heroku CLI
2. Login: `heroku login`
3. Create app: `heroku create your-app-name`
4. Add buildpacks:
   ```bash
   heroku buildpacks:add heroku/php
   heroku buildpacks:add heroku/nodejs
   ```
5. Add PostgreSQL: `heroku addons:create heroku-postgresql:mini`
6. Set environment variables
7. Deploy: `git push heroku main`

---

## 📋 Required Environment Variables

All platforms need these variables:

```env
# Application
APP_NAME=EduSphere
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

# Cache & Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis (if using)
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379

# Mail (configure based on your provider)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Storage
FILESYSTEM_DISK=public
```

---

## 🔧 Post-Deployment Checklist

After deploying, run these commands:

```bash
# Generate application key (if not set)
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed database (optional)
php artisan db:seed

# Create storage link
php artisan storage:link

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 🗑️ Removing Vercel Configuration

Since we're not using Vercel, you can remove:

```bash
# Remove Vercel-specific files
rm -rf api/
rm vercel.json
```

Or keep them if you want to try Vercel later with a different approach.

---

## 📚 Additional Resources

- [Railway Documentation](https://docs.railway.app)
- [Render Documentation](https://render.com/docs)
- [DigitalOcean App Platform](https://www.digitalocean.com/products/app-platform)
- [Laravel Forge Documentation](https://forge.laravel.com/docs)
- [Laravel Deployment Guide](https://laravel.com/docs/deployment)

---

## 💡 Recommendation

**For beginners:** Start with **Railway** - it's the easiest and has a free tier.

**For production:** Use **Laravel Forge** or **DigitalOcean App Platform** for better control and performance.

**For budget-conscious:** **Render** offers a good free tier with PostgreSQL.

