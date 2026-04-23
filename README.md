# EduSphere - School ERP SaaS Platform

Enterprise-level multi-tenant School ERP SaaS platform built with Laravel 12.

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+ (use `php8.2` if you have multiple PHP versions)
- MySQL 8.0+
- Redis 6.0+
- Node.js 18+ and NPM

### Installation

```bash
# 1. Install PHP dependencies
php8.2 /usr/bin/composer install

# 2. Install Node dependencies
npm install

# 3. Setup environment
cp .env.example .env
php8.2 artisan key:generate

# 4. Configure database in .env
# DB_DATABASE=edusphere
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# 5. Run migrations and seed
php8.2 artisan migrate --seed

# 6. Start development servers
php8.2 artisan serve    # Terminal 1
npm run dev             # Terminal 2
```

## 📋 Default Credentials

- **Super Admin**: `admin@edusphere.com` / `password`
- **School Admin**: `admin@demo.school.com` / `password`
- **Demo School Subdomain**: `demo`

## 🏗️ Architecture

- **Multi-Tenant**: Subdomain-based routing with data isolation
- **Roles**: Super Admin, School Admin, Teacher, Student, Parent
- **Frontend**: Livewire 3 + Tailwind CSS
- **Backend**: Laravel 12 with MySQL
- **Cache**: Redis
- **Queue**: Laravel Horizon

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/      # Super admin
│   │   ├── School/     # School admin
│   │   ├── Teacher/    # Teacher portal
│   │   ├── Student/    # Student portal
│   │   ├── Parent/     # Parent portal
│   │   └── Api/        # API endpoints
│   └── Middleware/     # Tenant, Role, School access
├── Models/             # Eloquent models
├── Services/           # Business logic
└── Traits/             # Reusable traits

routes/
├── web.php            # Main routes
├── admin.php          # Super admin routes
├── school.php         # School admin routes
├── teacher.php        # Teacher routes
├── student.php        # Student routes
├── parent.php         # Parent routes
└── api.php            # API routes
```

## 🛠️ Development

### Using Makefile (Recommended)

```bash
make install          # Install dependencies
make migrate          # Run migrations
make serve            # Start server
make test             # Run tests
```

### Manual Commands

```bash
# PHP 8.2 commands
php8.2 artisan migrate
php8.2 artisan serve
php8.2 artisan tinker

# Or use wrapper scripts
./composer-php82.sh install
./run-php82.sh artisan migrate
```

## 🔐 Multi-Tenant Setup

Each school has a unique subdomain:
- `school1.edusphere.com` → School 1
- `school2.edusphere.com` → School 2

Data is automatically isolated by `school_id` using global scopes.

## 📦 Key Packages

- **Laravel Passport** - API authentication
- **Spatie Permission** - Role & permission management
- **Spatie Activity Log** - Audit trail
- **Laravel Horizon** - Queue monitoring
- **Maatwebsite Excel** - Excel import/export
- **Laravel Octane** - High-performance server

## 🧪 Testing

### Fast unit checks

These do not require a database connection and are the quickest way to verify the
policy-based security hardening work.

```bash
php artisan test tests/Unit/Policies/AuthorizationPolicyTest.php
```

### Full application test suite

Most feature and service tests use `RefreshDatabase`, so they require a real test
database. By default `phpunit.xml` expects:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=edusphere_test
DB_USERNAME=root
DB_PASSWORD=
```

Recommended setup:

```bash
cp .env.testing.example .env.testing
php artisan key:generate --env=testing
```

Then create the `edusphere_test` database and run:

```bash
php artisan test
```

If you want to override the test database at runtime, you can do that too:

```bash
DB_CONNECTION=mysql \
DB_HOST=127.0.0.1 \
DB_PORT=3306 \
DB_DATABASE=edusphere_test \
DB_USERNAME=root \
DB_PASSWORD=root \
php artisan test tests/Feature/TenantIsolationTest.php
```

### Security-focused test targets

```bash
php artisan test tests/Unit/Policies/AuthorizationPolicyTest.php
php artisan test tests/Feature/TenantIsolationTest.php
php artisan test tests/Feature/SchoolAccessTest.php
php artisan test tests/Feature/TenantMiddlewareTest.php
php artisan test tests/Unit/Services/FeePaymentServiceTest.php
```

If the suite fails immediately with `SQLSTATE[HY000] [2002]`, the test database is
not reachable from the current environment yet.

## 📚 Documentation

- See `ARCHITECTURE.md` for system design
- See `DEPLOYMENT.md` for production setup
- See `.env.testing.example` for a database-backed PHPUnit template

## 📄 License

MIT License

# EduSphere
