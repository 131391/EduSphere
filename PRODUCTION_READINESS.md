# Production Readiness Checklist

Tracker for the work needed to take EduSphere from current state to production deploy.
Checkboxes get checked when the change lands on `main` and (where applicable) is verified in staging.

Severity: 🔴 critical / 🟠 high / 🟡 medium / 🟢 low.

---

## Phase 1 — Blockers (must finish before any production deploy)

- [ ] 🔴 **Tenantable null-safety.** [app/Traits/Tenantable.php](app/Traits/Tenantable.php) silently no-ops when `currentSchool` is unbound, returning cross-school data. Allow console + pre-auth + super_admin; throw in dev, fail-closed (zero rows) in prod for any other case; log every bypass.
- [ ] 🔴 **Author `ARCHITECTURE.md`** — referenced in [README.md](README.md) but missing. Cover tenant resolution, role model, request flow, data isolation guarantees.
- [ ] 🔴 **Author `DEPLOYMENT.md`** — referenced in [README.md](README.md) but missing. Cover prereqs, env vars, build, migrate, queue/Horizon, scheduler, cache, observability.
- [ ] 🔴 **Wire error tracking (Sentry).** No service today; production errors land in `storage/logs/` with no alerting. Install `sentry/sentry-laravel`, configure DSN per env, capture release/environment.
- [ ] 🔴 **Sweep `authorize()` calls into financial controllers first.** Policies exist but ~75% of controllers rely solely on role middleware. Start with [app/Http/Controllers/School/FeeController.php](app/Http/Controllers/School/FeeController.php), `FeePaymentController`, `WaiverController`, `LateFeeController`, then expand to all CRUD actions.
- [ ] 🔴 **Real CI deploy step.** [.github/workflows/ci.yml](.github/workflows/ci.yml) deploy job is `echo "Deploy to production server"`. Replace with the actual target (Forge / SSH+rsync / Railway — pick one).
- [ ] 🟠 **Production `.env` profile.** Set `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, `ENFORCE_HTTPS=true`, `LOG_CHANNEL=daily`, `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`. Document required keys in `DEPLOYMENT.md`.

---

## Phase 2 — Hardening (complete before launch; soft-launch acceptable)

- [ ] 🟠 **Drop `TenantMiddleware` cache TTL** from 300s → 60s in [app/Http/Middleware/TenantMiddleware.php](app/Http/Middleware/TenantMiddleware.php), and invalidate on `School::updated` via observer. Stale subscription/active state today persists up to 5 min.
- [ ] 🟠 **Cascade-on-delete on School FKs.** Deleting a school nukes students/fees/exams. Add `softDeletes()` to schools (already partially used) and switch hot FKs to `restrict`; require explicit data-export before hard delete.
- [ ] 🟠 **Route-model bindings scoped to `school_id`.** [SchoolAccessMiddleware](app/Http/Middleware/SchoolAccessMiddleware.php) checks the user, not the URL parameters. Forge a `{student}` ID from another school and the controller will load it. Add scoped bindings in `RouteServiceProvider` (or `bootstrap/app.php`).
- [ ] 🟠 **Fix XSS in `{!! session('warning') !!}`** in [resources/views/school/settings/session.blade.php](resources/views/school/settings/session.blade.php). Use `{{ }}`.
- [ ] 🟠 **Background jobs for heavy work.** No `app/Jobs/` exists. Move Excel imports, PDF report cards, fee-statement exports into `ShouldQueue` jobs. Otherwise large schools will hit request timeouts.
- [ ] 🟠 **Stripe footprint.** `.env` references `PAYMENT_GATEWAY=stripe` but only [RazorpayGateway.php](app/Services/PaymentGateways/RazorpayGateway.php) exists. Either implement `StripeGateway` or remove all Stripe references and have a `PaymentGatewayFactory` throw on unknown drivers.
- [ ] 🟠 **Production Docker image.** [docker/Dockerfile](docker/Dockerfile) and [docker-compose.yml](docker-compose.yml) are dev-only: empty MySQL password, no healthchecks, single-stage build, relative volumes. Add multi-stage build and a separate `docker/prod/Dockerfile`.
- [ ] 🟠 **Remove or commit to Vercel.** [vercel.json](vercel.json) is present but Laravel doesn't run on Vercel serverless. Pick a host (Forge / Railway / DigitalOcean / Fly) and delete the misleading file.
- [ ] 🟠 **Permissive file uploads.** e.g. [Receptionist/VisitorController](app/Http/Controllers/Receptionist/VisitorController.php) allows `id_proof => nullable|file|max:2048` (any extension). Whitelist mimes everywhere uploads are accepted.
- [ ] 🟠 **Throttle `/api/login`.** Web login throttles in [LoginController](app/Http/Controllers/Auth/LoginController.php); the Sanctum equivalent in [routes/api.php](routes/api.php) does not. Add `throttle:5,1`.

---

## Phase 3 — Module gaps (within 2–4 weeks of launch)

- [ ] 🟠 **Notifications/announcements module** — no `notices`/`announcements`/`events` tables, models, or controllers. Schools expect this; needs schema + per-role dashboards.
- [ ] 🟠 **REST API surface.** Only 3 read-only controllers in [app/Http/Controllers/Api/](app/Http/Controllers/Api/). Define the mobile/integration scope, then add CRUD with policies and OpenAPI spec.
- [ ] 🟡 **2FA for admin + finance roles.** No `two-factor`/`totp` references anywhere. Required given fee-handling. Use `pragmarx/google2fa-laravel` or Fortify's TFA.
- [ ] 🟡 **Spatie/laravel-backup is installed but inert.** No `config/backup.php`, no schedule, no S3 disk, no encryption. Publish config, schedule daily 02:00, encrypt to S3.
- [ ] 🟡 **Activity-log retention.** `ACTIVITY_LOG_CLEAN_OLDER_THAN_DAYS=90` is set but `activity:clean` isn't in [routes/console.php](routes/console.php) — table grows forever.
- [ ] 🟡 **Horizon config.** `HORIZON_ENABLED=true` but no published `config/horizon.php`. Tune supervisors/timeouts to actual server.
- [ ] 🟡 **Soft-delete consistency.** Many models use `SoftDeletes` (Student etc.) but downstream queries rarely call `withoutTrashed()`. Audit reports/payments to make sure trashed students aren't double-charged or hidden inconsistently.

---

## Phase 4 — Quality, observability, polish

- [ ] 🟡 **Controller / API endpoint tests.** Service-layer coverage is good; HTTP layer is mostly untested. Add feature tests for finance flows + role-based 403s.
- [ ] 🟡 **Structured logging + alert routing.** Today: `single` channel, no JSON, no Slack. Add `daily` rotation + a Slack channel at `critical`.
- [ ] 🟡 **Stop documenting Livewire 3 if we don't use it.** [README.md](README.md) advertises "Livewire 3 + Tailwind"; only generic Livewire components exist, the app is Blade + AJAX. Either migrate role pages to Livewire or amend the docs.
- [ ] 🟡 **Stub controllers.** [School/SupportController.php](app/Http/Controllers/School/SupportController.php) (13 lines) and [Student/DashboardController.php](app/Http/Controllers/Student/DashboardController.php) (17 lines) — finish or remove.
- [ ] 🟡 **README structure drift.** Document the two undeclared role portals (Receptionist, Librarian) and their route files.
- [ ] 🟢 **Telescope routes.** Disabled in `.env`, but verify they aren't registered when `APP_ENV=production`.
- [ ] 🟢 **Hostel `whereRaw` review.** Low risk (hardcoded columns) but rewrite as `leftJoin` + `havingRaw` for clarity.
- [ ] 🟢 **`SECURITY.md`** for vulnerability disclosure.

---

## Owner / dates

Add a row when work starts. Keep this tight — checked items stay; speculative items don't belong here.

| Item | Owner | Started | Landed | Notes |
|---|---|---|---|---|
| | | | | |
