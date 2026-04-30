# EduSphere Architecture

This document describes how EduSphere is built. It is the document a new
engineer should read before writing code.

For deployment instructions, see [DEPLOYMENT.md](DEPLOYMENT.md).
For the production-readiness backlog, see [PRODUCTION_READINESS.md](PRODUCTION_READINESS.md).

---

## 1. Stack

| Layer | Choice |
|---|---|
| Language / runtime | PHP 8.3 |
| Framework | Laravel 12 |
| Frontend | Blade templates + Tailwind CSS, Vite, Alpine.js, AJAX (DataTables). Livewire 3 is installed but used only for shared UI primitives — see §6. |
| Database | MySQL 8 |
| Cache / queue broker | Redis 6 |
| Queue worker / dashboard | Laravel Horizon |
| API auth | Laravel Sanctum (Passport scaffolding present but unused) |
| Roles & policies | First-party (see §3); `spatie/laravel-permission` migration is included for compatibility but the project's own `Role` model is the source of truth |
| Audit log | `spatie/laravel-activitylog` |
| Excel imports/exports | `maatwebsite/excel` |
| Payment gateway | Razorpay (only live integration today) |

---

## 2. Multi-tenancy

EduSphere is a single-database, **shared-schema** multi-tenant SaaS. Every
tenant is one `School` row; every business table carries a `school_id`
column; every relevant model applies a global scope that adds
`where school_id = currentSchool->id` to every query.

### 2.1 Tenant resolution

The active school is identified once per request by
[TenantMiddleware](app/Http/Middleware/TenantMiddleware.php) and stored in the
container under the `currentSchool` binding.

Resolution strategy is configurable via `config/tenant.php` →
`identification_method` (`subdomain` | `domain` | `path` | `header`). The
deployed configuration is **subdomain**:

```
school1.edusphere.com  →  School where subdomain = 'school1'
admin.edusphere.com    →  bypassed (super-admin host, see §3)
www.edusphere.com      →  bypassed (marketing host)
api.edusphere.com      →  bypassed (Sanctum auth provides tenant later)
```

Resolved `School` objects are cached in Redis under
`school.subdomain.{subdomain}` for 300 seconds. **This TTL is too long for a
disabled school to stop serving traffic** — see
[PRODUCTION_READINESS.md](PRODUCTION_READINESS.md) Phase 2.

If the school is found but has `status != Active` or its subscription has
expired, the middleware returns the `errors.subscription-inactive` view with
HTTP 403 instead of binding it.

### 2.2 Data isolation: `Tenantable` trait

Every business model uses the [Tenantable](app/Traits/Tenantable.php) trait,
which adds two boot-time hooks:

- A global scope that filters every `select` by `school_id`.
- A `creating` hook that auto-fills `school_id` from `currentSchool`.

The trait is the **single point that prevents cross-tenant data leakage** in
the ORM layer. Behavior matrix:

| Caller | Behavior |
|---|---|
| `currentSchool` is bound (the normal tenant-scoped HTTP request) | Scope adds `where school_id = currentSchool->id`. |
| Console (`php artisan ...`, scheduler, Horizon jobs) | No scope applied. Console code is trusted and often loops over schools. |
| HTTP request, no `currentSchool` bound | No scope applied; emits `Log::info('Tenantable scope skipped: tenant context not bound', ...)` with the model + route + URL. |

Why the HTTP-no-tenant branch only logs and doesn't throw or return zero
rows: this trait runs *inside* the User model's own retrieval (User uses
Tenantable). Calling `auth()->user()` or `auth()->id()` from here causes
infinite recursion because both ultimately call `User::find($id)`, which
re-enters this scope. So we cannot distinguish "super admin browsing the
admin portal" from "school user on a misconfigured route" inside the
scope itself. The log line gives operations the visibility to catch
genuinely-misconfigured routes; tightening this further requires binding
`currentSchool` from `user.school_id` earlier in the request lifecycle —
tracked in [PRODUCTION_READINESS.md](PRODUCTION_READINESS.md) Phase 1.

Legitimate authenticated routes that hit this branch today:

- The post-login `/dashboard` role-dispatcher in `routes/web.php`.
- The super-admin portal (`routes/admin.php`) — by design.

Models that opt into the trait (49 today): `User`, `Student`, `Teacher`,
`Fee`, `FeePayment`, `Result`, `Attendance`, `Timetable`, `Exam`,
`Hostel*`, `Transport*`, `Book*`, etc.

`School` itself is **not** Tenantable — super-admin and the tenant
middleware itself need to query it without a scope.

### 2.3 Authorization on top of tenancy

Two middlewares run after `TenantMiddleware`:

- [SchoolAccessMiddleware](app/Http/Middleware/SchoolAccessMiddleware.php) —
  asserts `$user->canAccessSchool(currentSchool->id)`. Without this, a user
  who belongs to school A could log in via school B's subdomain.
- [RoleMiddleware](app/Http/Middleware/RoleMiddleware.php) — asserts the
  user holds one of the role slugs declared in the route
  (`role:school_admin,librarian`).

⚠️ Neither middleware validates that **route parameters** belong to the
current school (`/students/{student}` accepts any ID; the controller loads
it via `findOrFail` and the global scope does the filtering). This is
defense-in-depth only — see Phase 2.

---

## 3. Roles and portals

Seven roles, each with its own URL prefix, route file, and controller
folder.

| Role | Prefix | Route file | Tenant-scoped? |
|---|---|---|---|
| `super_admin` | `/admin` | [routes/admin.php](routes/admin.php) | No (platform-wide) |
| `school_admin` | `/school` | [routes/school.php](routes/school.php) | Yes |
| `librarian` | `/school` | [routes/library.php](routes/library.php) | Yes (shares prefix with school_admin) |
| `teacher` | `/teacher` | [routes/teacher.php](routes/teacher.php) | Yes |
| `student` | `/student` | [routes/student.php](routes/student.php) | Yes |
| `parent` | `/parent` | [routes/parent.php](routes/parent.php) | Yes |
| `receptionist` | `/receptionist` | [routes/receptionist.php](routes/receptionist.php) | Yes |

All seven groups are loaded from [routes/web.php](routes/web.php) under
`Route::prefix(...)->middleware([...])->group(require ...)`. The middleware
stack for tenant-scoped portals is:

```
['auth', 'tenant', 'school.access', 'role:<role>']
```

Super-admin uses `['auth', 'role:super_admin']` only — no tenant binding.
The `Tenantable` global scope falls through (with a log line) when
`currentSchool` isn't bound, so super-admin queries return data across
schools as intended. See §2.2.

`User::isSuperAdmin()` requires `school_id IS NULL` on the user row.

---

## 4. Layered code structure

```
app/
├── Http/
│   ├── Controllers/{Admin, School, Teacher, Student, Parent, Receptionist, Api, Auth, Webhooks}/
│   ├── Middleware/        # Tenant, SchoolAccess, Role, ResolvePublicTenant, …
│   └── Requests/          # Form-request validation
├── Models/                # Eloquent models, most use Tenantable
├── Services/              # Business logic — controllers stay thin
│   ├── BaseService.php
│   ├── TenantService.php
│   ├── PaymentGateways/RazorpayGateway.php
│   └── School/
│       ├── Examination/{ExamService, ResultService, TabulationService, ReportCardService, …}
│       ├── FeePaymentService.php  (idempotent — see §7)
│       ├── AdmissionService.php
│       ├── LibraryService.php
│       ├── HostelService.php
│       ├── TransportRouteService.php / StudentTransportService.php / TransportIntegrityService.php
│       └── … (~38 domain services)
├── Policies/              # 13 policies covering exams, fees, students, results, books, schools, users
├── Traits/                # Tenantable, Cacheable, HasUuid, Searchable, Sortable, Exportable, …
├── Jobs/                  # ⚠️ does not exist yet — heavy work runs synchronously
├── Notifications/         # Queue-able notifications (e.g. FeeDueReminder)
├── Console/Commands/      # ApplyLateFees, GenerateFacilityFees, SendFeeReminders, …
└── Exceptions/
    └── TenantResolutionException.php
```

### Conventions

- **Controllers stay thin.** Anything more complex than a CRUD shape lives
  in a service. Examples: [FeePaymentService](app/Services/School/FeePaymentService.php)
  (323 lines), [LibraryService](app/Services/School/LibraryService.php),
  [AdmissionService](app/Services/School/AdmissionService.php).
- **Policies first, middleware second.** Role middleware proves the user
  *may* enter the route; policies prove the user *may* act on this row.
  Today most controllers rely on middleware alone — closing this gap is
  Phase 1.
- **Form requests** for non-trivial validation. Don't `$request->validate(...)`
  inline in controllers when the rule list is more than ~3 keys.
- **`Tenantable` is mandatory** on any new model that has a `school_id`
  column. Don't add `where('school_id', ...)` calls by hand.

---

## 5. Request lifecycle

```
HTTPS request
   │
   ▼
TrustProxies / TrimStrings / EncryptCookies / VerifyCsrfToken (web)
   │
   ▼
auth                      ← user resolved
   │
   ▼
tenant                    ← School resolved by subdomain, bound to container
   │
   ▼
school.access             ← user.canAccessSchool(currentSchool->id)
   │
   ▼
role:<role>               ← user holds the required role slug
   │
   ▼
Controller action
   │
   ├── Form request validation (where used)
   ├── Policy check ($this->authorize(...))
   ├── Service call (business logic, transactions)
   │     └── Eloquent → Tenantable global scope filters by school_id
   │
   └── View / JSON / redirect
```

API requests run a similar stack with `auth:sanctum` instead of session
auth and skip CSRF.

---

## 6. Frontend

The marketing claim is "Livewire 3 + Tailwind". The reality:

- Pages render as classic Blade views from controller actions.
- Interactivity is provided by **Alpine.js** for in-page state, the
  **DataTables** jQuery plugin for index pages (see
  [HasAjaxDataTable trait](app/Traits/HasAjaxDataTable.php)), and plain
  `fetch` for AJAX endpoints.
- Livewire 3 is installed and used for a small set of *shared UI primitives*
  in [app/Livewire/Components/](app/Livewire/Components/) — `Modal`,
  `Alert`, `Pagination`, `LoadingIndicator`, `SearchFilter`. Page-level
  Livewire components do not exist (`app/Livewire/Forms/` is empty,
  `app/Livewire/Pages/` has only a generic dashboard).

This is fine, but the README/docs should match reality — see Phase 4.

Vite handles asset bundling. Output is `public/build/` (referenced from
Blade via `@vite(...)`). `public/storage` is a symlink to
`storage/app/public`.

---

## 7. Money flows

Three concerns dominate the financial code paths:

1. **Idempotency.** `FeePaymentService` and the Razorpay webhook controller
   both check existing transaction state before applying a payment so a
   duplicate webhook delivery cannot double-credit a fee. There is a unit
   test ([FeePaymentIdempotencyTest](tests/Unit/Services/FeePaymentIdempotencyTest.php)).
2. **Reversals.** Refunds/voids are tested separately
   ([FeePaymentReversalTest](tests/Unit/Services/FeePaymentReversalTest.php)).
3. **Webhook signature verification.** Razorpay requests are verified with
   HMAC-SHA256 in
   [Webhooks/RazorpayWebhookController](app/Http/Controllers/Webhooks/RazorpayWebhookController.php)
   before any state change. CSRF is exempted only for `webhooks/*` in
   [VerifyCsrfToken](app/Http/Middleware/VerifyCsrfToken.php).

The Stripe gateway is referenced in `.env.example` but **not implemented**.
A `PAYMENT_GATEWAY=stripe` setting today silently breaks payment processing.

---

## 8. Background work

- **Scheduler** (run from `php artisan schedule:run`, expected as a 1-min
  cron in production): drives the commands in
  [app/Console/Commands/](app/Console/Commands/) — `ApplyLateFees`,
  `GenerateFacilityFees`, `SendFeeReminders`,
  `SendOverdueLibraryNotifications`, `SyncExamStatuses`, `SyncMasterIds`.
- **Queue (Redis via Horizon).** Notifications implementing `ShouldQueue`
  flow through Horizon. Heavy synchronous code — Excel imports, report-card
  PDF generation, fee statement exports — does **not** yet flow through
  jobs. This is Phase 2.

---

## 9. Where the audit found risk

Concise list — the explanations live in
[PRODUCTION_READINESS.md](PRODUCTION_READINESS.md).

- Tenant scope was silent on misconfiguration. **Partial fix:** [Tenantable](app/Traits/Tenantable.php) now logs (`Log::info`) every authenticated query made without a `currentSchool` binding so misconfigured routes are visible. Strictly failing closed requires binding `currentSchool` from `user.school_id` earlier in the request — tracked as Phase 1 work; see §2.2.
- Tenant cache TTL is 300 s. (Phase 2)
- Route parameters are not scoped to `school_id`. (Phase 2)
- Most controllers don't call `$this->authorize(...)` despite policies existing. (Phase 1, finance first)
- `.env` defaults are debug-mode-friendly, not production. (Phase 1)
- Stripe is referenced but not implemented. (Phase 2)
- No error tracker, no real CI deploy step, no `app/Jobs/`, no notifications module. (Phase 1–3)

---

## 10. Glossary

- **Tenant / School** — used interchangeably. One row in `schools`.
- **Subdomain bypass list** — `www`, `admin`, `api`. Hosts on this list
  skip tenant identification.
- **Tenantable** — the trait that enforces `school_id` filtering and
  auto-filling on a model.
- **Super admin** — `User` with `role = super_admin` and `school_id IS NULL`.
  Has cross-tenant read/write access by design.
- **Portal** — the per-role URL prefix + route file + controllers folder
  trio (e.g. "the school admin portal").
