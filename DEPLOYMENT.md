# Deployment

How to deploy EduSphere to production. Read [ARCHITECTURE.md](ARCHITECTURE.md)
first to understand what you are deploying.

The open production gaps still being worked are tracked in
[PRODUCTION_READINESS.md](PRODUCTION_READINESS.md). **Do not ship to a paying
customer until the Phase 1 items there are checked off.**

---

## 1. Prerequisites

- PHP 8.3 (with `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `intl`,
  `mbstring`, `openssl`, `pdo_mysql`, `redis`, `tokenizer`, `xml`, `zip`,
  `gd`)
- Composer 2
- Node.js 18 LTS + npm
- MySQL 8.0
- Redis 6.x
- Supervisor (for Horizon worker)
- A scheduler trigger every 60 s (cron, systemd timer, or platform cron)

For app servers, PHP-FPM behind Nginx is the supported topology. **Do not
serve production traffic with `php artisan serve` or `octane:start` — that
is a local-dev shortcut.**

---

## 2. Hosting target

The repo currently contains a [Procfile](Procfile) (Heroku-style) and a
[vercel.json](vercel.json). **Vercel is not a viable target** for a
traditional Laravel server-side app; the file is a leftover and is being
removed.

Pick one:

| Target | Notes |
|---|---|
| **Laravel Forge + DigitalOcean / AWS / Hetzner** | Recommended path. Forge handles PHP-FPM, Nginx, Let's Encrypt, queue worker, scheduler, and zero-downtime deploys. |
| **Railway / Render** | Single-region SaaS. Easy first deploy, watch out for Redis / cron pricing. |
| **Self-hosted Ubuntu 22.04 + Docker Compose** | Use a hardened production Compose file (see §7) — the current `docker-compose.yml` is dev-only. |

The rest of this doc is written for Forge but applies elsewhere with minor
substitution.

---

## 3. DNS

Multi-tenancy is **subdomain-based** (see ARCHITECTURE.md §2.1). Configure:

```
A     edusphere.com           →  load balancer / app IP
A     *.edusphere.com         →  load balancer / app IP   ← wildcard, required
A     admin.edusphere.com     →  same                     ← reserved (super-admin)
A     api.edusphere.com       →  same                     ← reserved
A     www.edusphere.com       →  same                     ← reserved (marketing)
```

Issue a **wildcard TLS cert** for `*.edusphere.com` (Let's Encrypt DNS-01
or a paid wildcard). Without it, every new school subdomain will fail TLS.

The app refuses to bind a school for hosts whose first label is `www`,
`admin`, or `api` — see
[TenantMiddleware::identifyBySubdomain](app/Http/Middleware/TenantMiddleware.php).

---

## 4. Environment variables

Start from `.env.example` and override the keys below. **Never commit
the production `.env`.**

### 4.1 Required

```bash
APP_NAME=EduSphere
APP_ENV=production
APP_KEY=                       # php artisan key:generate --show, paste here
APP_DEBUG=false                # MUST be false in production
APP_URL=https://edusphere.com
APP_TIMEZONE=Asia/Kolkata      # whatever you prefer
ASSET_URL=https://edusphere.com
ENFORCE_HTTPS=true

LOG_CHANNEL=daily
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=…
DB_PORT=3306
DB_DATABASE=edusphere
DB_USERNAME=…
DB_PASSWORD=…

REDIS_CLIENT=phpredis
REDIS_HOST=…
REDIS_PORT=6379
REDIS_PASSWORD=…
REDIS_CACHE_DB=1
REDIS_QUEUE_DB=2

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=.edusphere.com  # leading dot — shares session across subdomains

MAIL_MAILER=smtp                # or ses
MAIL_HOST=…
MAIL_PORT=587
MAIL_USERNAME=…
MAIL_PASSWORD=…
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@edusphere.com
MAIL_FROM_NAME=EduSphere

TENANT_IDENTIFICATION_METHOD=subdomain
TENANT_CACHE_TTL=60             # see PRODUCTION_READINESS.md Phase 2

SANCTUM_STATEFUL_DOMAINS=edusphere.com,*.edusphere.com

# Razorpay
RAZORPAY_KEY=…
RAZORPAY_SECRET=…
RAZORPAY_WEBHOOK_SECRET=…
PAYMENT_GATEWAY=razorpay        # stripe is NOT implemented — do not set

# Horizon
HORIZON_ENABLED=true
HORIZON_BALANCE=auto
HORIZON_MAX_PROCESSES=10
HORIZON_MEMORY_LIMIT=128
HORIZON_TIMEOUT=60
TELESCOPE_ENABLED=false         # MUST be false in production

# Activity log
ACTIVITY_LOGGER_ENABLED=true
ACTIVITY_LOG_CLEAN_OLDER_THAN_DAYS=90
```

### 4.2 Strongly recommended (Phase 1)

```bash
SENTRY_LARAVEL_DSN=https://…@sentry.io/…
SENTRY_ENVIRONMENT=production
SENTRY_RELEASE=                 # injected by deploy script
LOG_SLACK_WEBHOOK_URL=          # critical-only Slack channel
```

### 4.3 Storage

For a single-server install, the default `local` filesystem disk is fine.
For multi-server or for backups, configure S3:

```bash
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=…
AWS_SECRET_ACCESS_KEY=…
AWS_DEFAULT_REGION=ap-south-1
AWS_BUCKET=edusphere-prod
AWS_USE_PATH_STYLE_ENDPOINT=false

BACKUP_DISK=s3
BACKUP_NOTIFICATION_EMAIL=ops@edusphere.com
BACKUP_NOTIFICATION_ON_FAILURE=true
BACKUP_CLEAN_OLDER_THAN_DAYS=30
```

### 4.4 What stays default

`APP_FAKER_LOCALE`, `BCRYPT_ROUNDS`, `BROADCAST_*`, `MEMCACHED_*`,
`AWS_USE_PATH_STYLE_ENDPOINT`. Don't touch unless you have a reason.

---

## 5. First-time deploy

```bash
# On the server, as the deploy user, in the app root.

git clone git@github.com:…/EduSphere.git .
cp .env.production .env                  # populate first per §4

composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build

php artisan key:generate                 # only if APP_KEY is not pre-set
php artisan storage:link
php artisan migrate --force
php artisan db:seed --force --class=ProductionSeeder   # roles + super admin

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775   storage bootstrap/cache
```

After the seeder, **rotate the super-admin password** before exposing the
host publicly. The seeded credentials (`admin@edusphere.com` / `password`)
are documented in [README.md](README.md) and must not survive on a
production box.

---

## 6. Subsequent deploys

Forge or any deploy tool should run roughly:

```bash
git pull origin main

composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

php artisan horizon:terminate            # workers reload with new code
php artisan queue:restart                # belt + suspenders
```

Until the CI deploy step is wired up
([PRODUCTION_READINESS.md](PRODUCTION_READINESS.md) Phase 1), this is run
manually. The script exists today as a placeholder
(`echo "Deploy to production server"` in
[.github/workflows/ci.yml](.github/workflows/ci.yml)).

---

## 7. Process model on the server

Three persistent processes plus the web server:

### 7.1 Nginx + PHP-FPM
Standard Laravel virtualhost. Document root is `public/`. Make sure
`fastcgi_read_timeout` is at least `120s` for Excel imports until those
move to jobs.

### 7.2 Horizon (queue worker)

Run via Supervisor:

```ini
; /etc/supervisor/conf.d/edusphere-horizon.conf
[program:edusphere-horizon]
process_name=%(program_name)s
command=php /var/www/edusphere/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/edusphere/horizon.log
stopwaitsecs=3600
```

Confirm at `https://edusphere.com/horizon` (auth-gated) that workers are
green after every deploy.

### 7.3 Scheduler

Single cron entry:

```cron
* * * * * www-data cd /var/www/edusphere && php artisan schedule:run >> /dev/null 2>&1
```

This drives the commands in [app/Console/Commands/](app/Console/Commands/)
— fee reminders, late-fee application, library overdue notices, exam
status sync. Without this cron, none of those run.

---

## 8. Health, observability, alerting

- **Liveness:** `GET /up` returns `200` from Laravel's built-in health
  endpoint (registered in [bootstrap/app.php](bootstrap/app.php)).
  Wire this to your load balancer.
- **Sentry:** required for production (Phase 1). Without it, errors only
  reach `storage/logs/laravel.log` on the box.
- **Horizon dashboard:** `/horizon` — protect with the
  `HorizonServiceProvider::gate()` call so only super-admin can access.
- **MySQL slow log:** enable on the DB host with `long_query_time = 0.5`.
- **Logs:** rotate `storage/logs/*.log` daily; ship to your aggregator.

---

## 9. Backups

`spatie/laravel-backup` is in `composer.json` but **not configured** —
this is a Phase 3 item. Until then, configure native MySQL backups
(`mysqldump` to S3 + 7-day retention) at the database host.

When Spatie backup is wired up:

```cron
0 2 * * * www-data cd /var/www/edusphere && php artisan backup:run >> /dev/null 2>&1
0 3 * * * www-data cd /var/www/edusphere && php artisan backup:clean >> /dev/null 2>&1
```

---

## 10. Onboarding a new school (tenant)

1. Insert a row into `schools` with a unique `subdomain`, `status =
   active`, and a non-expired subscription.
2. Create the school admin via the super-admin UI at
   `https://admin.edusphere.com/admin/users` (the user must have
   `school_id` set).
3. The school is reachable at `https://{subdomain}.edusphere.com`. Wildcard
   DNS + wildcard cert handle the host without any deploy step.
4. The first admin login forces a password reset (`must_change_password`
   flag on `users`).

---

## 11. Decommissioning a school

Today, `cascadeOnDelete()` on most foreign keys means **deleting a school
row deletes all of its students, fees, exams, etc.** Until Phase 2 closes
this gap (soft-delete on schools, `restrict` on hot FKs), the safe
procedure is:

1. Run an export (per-school SQL dump filtered by `school_id`).
2. Set the school to `status = inactive`. The tenant middleware will then
   show the inactive-subscription page instead of serving traffic.
3. **Do not** issue `DELETE FROM schools WHERE id = …`.

---

## 12. Rollback

```bash
# On the server, as the deploy user.
git reset --hard <previous-commit>
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci && npm run build

# Reverse only the migrations introduced by the bad deploy.
php artisan migrate:rollback --step=<n> --force

php artisan config:cache route:cache view:cache event:cache
php artisan horizon:terminate
```

If the bad deploy ran a destructive migration (column drop, table drop),
rolling forward with a hotfix is usually safer than rolling back. Always
take a `mysqldump` immediately before running migrations on production.

---

## 13. Security baseline checklist

Before pointing real DNS at a fresh deploy, confirm:

- [ ] `APP_DEBUG=false`, `APP_ENV=production`.
- [ ] `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`.
- [ ] `TELESCOPE_ENABLED=false`.
- [ ] HTTPS enforced; HSTS sent by Nginx.
- [ ] Wildcard cert in place for `*.edusphere.com`.
- [ ] Default super-admin credentials rotated.
- [ ] `/horizon` and `/telescope` (if present) gated to super-admin only.
- [ ] DB user has only the privileges the app needs (no `SUPER`, no
      `FILE`).
- [ ] Outbound SMTP uses TLS.
- [ ] Razorpay webhook URL is set in the Razorpay dashboard and the
      `RAZORPAY_WEBHOOK_SECRET` matches.
- [ ] Sentry receiving a deliberately-thrown test exception.
- [ ] Backups have been restored at least once into a scratch DB.
