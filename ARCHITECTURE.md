# System Architecture

## Multi-Tenant Design

### Tenant Identification
- **Subdomain-based**: `school1.edusphere.com`
- **Domain-based**: Custom domains (optional)
- **Path-based**: `/school/school1` (alternative)

### Data Isolation
- Shared database with `school_id` foreign keys
- Global scopes automatically filter by school
- Middleware validates school access

### Login Separation
1. User visits subdomain → TenantMiddleware identifies school
2. User logs in → System validates `user.school_id === current_school.id`
3. Super admin can access all schools (`school_id = NULL`)

## Database Schema

### Core Tables
- `schools` - Tenant information
- `users` - All user accounts (unified table)
- `students`, `teachers`, `parents` - Role-specific profiles
- `classes`, `sections`, `subjects` - Academic structure
- `fees`, `attendance`, `exams`, `results` - Academic data
- `academic_years` - Academic year management

### Relationships
```
School (1) → (N) Users, Students, Teachers, Classes, Fees
Student (N) → (1) School, Class, Section, User
Student (N) → (M) Parents
Class (N) → (M) Subjects
```

## Authentication & Authorization

### User Roles
1. **Super Admin** - Platform management (`/admin/*`)
2. **School Admin** - School management (`/school/*`)
3. **Teacher** - Teaching staff (`/teacher/*`)
4. **Student** - Student portal (`/student/*`)
5. **Parent** - Parent portal (`/parent/*`)

### Access Control
- Role-based middleware on routes
- School access validation
- Spatie Permission for fine-grained permissions

## Routing Strategy

- **Admin Routes**: `/admin/*` - No tenant middleware
- **School Routes**: `/school/*` - Tenant + School access middleware
- **Teacher Routes**: `/teacher/*` - Tenant + Role middleware
- **Student Routes**: `/student/*` - Tenant + Role middleware
- **Parent Routes**: `/parent/*` - Tenant + Role middleware
- **API Routes**: `/api/v1/*` - Passport authentication

## Performance

### Caching
- Redis for sessions and cache
- School identification cached (1 hour TTL)
- Query result caching

### Queue
- Laravel Horizon for queue management
- Background jobs for emails, reports, exports

### Optimization
- Database indexes on foreign keys
- Eager loading relationships
- Route and config caching in production

## Security

- CSRF protection
- SQL injection prevention (Eloquent)
- XSS prevention
- Rate limiting
- Audit logging (Spatie Activity Log)
- Data encryption at rest

## Frontend

- **Vue.js 3** - Component framework
- **Inertia.js** - Server-side routing
- **Tailwind CSS** - Utility-first styling
- **Vite** - Build tool

## Deployment

- PHP 8.2+ with OPcache
- MySQL 8.0+ with proper indexes
- Redis for cache and sessions
- Nginx with SSL
- Laravel Horizon for queues
- Automated backups (Spatie Backup)

