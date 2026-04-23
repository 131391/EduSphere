# Laravel SaaS Audit Findings And Recommended Solutions

This document captures the main findings from a deep audit of the EduSphere Laravel SaaS codebase and pairs each finding with practical remediation guidance.

Scope covered:
- Laravel coding standards and maintainability
- Multi-tenancy and tenant isolation
- Authentication and authorization
- Security and OWASP-style risks
- Billing and subscription logic
- Business logic correctness
- Performance, scaling, and operational readiness

## Executive Summary

The application has a solid foundation for a school-focused SaaS product:
- Laravel 12, service classes, request validation, and tenant-aware middleware are in place
- Most tenant-facing web queries are manually scoped by `school_id`
- Core workflows such as admissions and fee collection attempt to use transactions

The biggest concerns are architectural consistency and security boundaries:
- The API layer is materially weaker than the web layer
- Tenant isolation is based on developer discipline instead of being enforced everywhere by default
- Billing and subscription automation are only partially implemented
- Some workflows can report failure after already committing data
- File copy/upload flows trust user-supplied storage paths more than they should

## Priority Matrix

### P0 - Fix Immediately

1. Tenant API can expose school data to the wrong authenticated user
2. Fee collection can commit records and still return an error
3. File-copy flows can be abused across tenants on shared storage

### P1 - Fix Next

1. Replace role-only checks with real authorization policies/permissions
2. Harden tenant isolation so it does not depend on manual query discipline
3. Correct broken student update flow
4. Align product claims, docs, and actual implementation

### P2 - Schedule Soon

1. Implement real billing/subscription lifecycle handling
2. Improve automated test setup and coverage
3. Reduce duplicated controller logic between school and receptionist modules
4. Strengthen indexing and query consistency review across the codebase

## Findings And Solutions

### 1. API tenant boundary is too weak

Severity: Critical

Relevant files:
- [routes/api.php](/var/projects/EduSphere/routes/api.php:21)
- [app/Http/Controllers/Api/AuthController.php](/var/projects/EduSphere/app/Http/Controllers/Api/AuthController.php:16)
- [app/Http/Controllers/Api/StudentController.php](/var/projects/EduSphere/app/Http/Controllers/Api/StudentController.php:14)
- [app/Http/Controllers/Api/FeeController.php](/var/projects/EduSphere/app/Http/Controllers/Api/FeeController.php:14)
- [app/Http/Controllers/Auth/LoginController.php](/var/projects/EduSphere/app/Http/Controllers/Auth/LoginController.php:62)

What is happening:
- Public API registration creates an active user with no tenant membership and no role.
- Protected tenant API routes require only `auth:sanctum` and `tenant`.
- The API login flow does not enforce the school/subdomain membership check that exists in the web login flow.
- Any active token holder may be able to access tenant data resolved by subdomain or header-based tenant resolution.

Why this matters:
- This is a direct tenant data exposure risk.
- It breaks the most important SaaS guarantee: one tenant cannot read another tenant's private records.

Recommended solution:
- Remove public `/api/register` unless self-signup is a real business requirement.
- Add a dedicated API authorization layer:
  - `auth:sanctum`
  - `tenant`
  - `school.access`
  - policy or permission middleware for each resource
- Mirror the web login's tenant-membership check inside API authentication.
- Scope tokens by ability. Examples:
  - `students:read`
  - `fees:read`
  - `fees:collect`
- Reject token issuance for users with:
  - no `school_id`
  - no active role
  - inactive or suspended tenant membership
- If external integrations are needed, create tenant-bound service accounts instead of generic user tokens.

Suggested implementation steps:
1. Delete or disable `POST /api/register`.
2. Add `school.access` to protected API route groups.
3. Add API policies or permission middleware per endpoint.
4. Update `AuthController@login()` to verify the authenticated user belongs to the resolved school.
5. Add tests for:
   - same user, wrong tenant host
   - user with null `school_id`
   - user with no role
   - token abilities

### 2. Fee collection can succeed in the database but fail in the UI

Severity: Critical

Relevant files:
- [app/Services/School/FeePaymentService.php](/var/projects/EduSphere/app/Services/School/FeePaymentService.php:21)
- [app/Http/Controllers/School/FeePaymentController.php](/var/projects/EduSphere/app/Http/Controllers/School/FeePaymentController.php:130)

What is happening:
- The payment transaction is committed.
- After commit, activity logging references an undefined `$student` variable.
- That exception is caught and converted into a failure response.
- The controller also expects `receipt_no` at the wrong response level.

Why this matters:
- Operators can be told a payment failed even though it already posted.
- A retry can create duplicate payment records and double collection.
- This is a billing integrity issue, not just a bug.

Recommended solution:
- Make payment posting atomic from the caller's perspective.
- Never place non-critical logging after commit in the same try/catch that controls user-facing success.
- Return a strong typed result DTO or a normalized response structure.

Suggested implementation steps:
1. Load the `Student` model before activity logging or log against the fee records directly.
2. Move activity logging into:
   - the transaction before commit if it must be atomic, or
   - a queued listener after a guaranteed success response if it is non-critical
3. Return:
   - `success`
   - `message`
   - `data.receipt_no`
   - `data.total_amount`
4. Update the controller to read `data.receipt_no`.
5. Add idempotency protection for repeated submissions:
   - request UUID
   - unique payment command key
   - duplicate receipt suppression
6. Add tests for:
   - successful multi-fee collection
   - repeated submission with same request key
   - activity log failure should not turn success into error

### 3. File copy and replacement logic trusts client-supplied storage paths

Severity: Critical

Relevant files:
- [app/Traits/HandlesFileCopies.php](/var/projects/EduSphere/app/Traits/HandlesFileCopies.php:18)
- [app/Http/Controllers/School/AdmissionController.php](/var/projects/EduSphere/app/Http/Controllers/School/AdmissionController.php:321)
- [app/Http/Controllers/School/StudentRegistrationController.php](/var/projects/EduSphere/app/Http/Controllers/School/StudentRegistrationController.php:320)
- [app/Http/Controllers/School/StudentRegistrationController.php](/var/projects/EduSphere/app/Http/Controllers/School/StudentRegistrationController.php:490)

What is happening:
- Request payloads can supply existing storage paths such as `father_photo_path` or `enquiry_*`.
- The app copies or deletes those paths if they exist on the shared public disk.
- The code does not verify that the source file belongs to the current tenant or current record.

Why this matters:
- Cross-tenant file disclosure becomes possible if a path is guessed or leaked.
- Another tenant's file may be copied into the current tenant's record.
- Existing files may be deleted unintentionally if a malicious or incorrect path is supplied.

Recommended solution:
- Stop accepting arbitrary storage paths from the client.
- Replace path-based trust with record-based trust.

Suggested implementation steps:
1. Accept source record IDs, not raw file paths.
2. Resolve the file server-side from a tenant-scoped model lookup.
3. Enforce that all copied files originate from:
   - the same `school_id`
   - an allowed source model
   - an allowed field whitelist
4. Store uploads in tenant-prefixed folders only.
5. Consider private disks plus signed download routes for sensitive parent/student documents.
6. Add tests for cross-tenant copy attempts and invalid path injection.

### 4. Authorization is too coarse-grained

Severity: High

Relevant files:
- [app/Http/Middleware/RoleMiddleware.php](/var/projects/EduSphere/app/Http/Middleware/RoleMiddleware.php:18)
- [app/Models/User.php](/var/projects/EduSphere/app/Http/Middleware/RoleMiddleware.php:18)
- [composer.json](/var/projects/EduSphere/composer.json:21)

What is happening:
- Access control is mostly based on route-level role slug checks.
- The app installs `spatie/laravel-permission` but does not appear to use it for authorization decisions.
- No meaningful policy/Gate layer was found for resource actions.

Why this matters:
- "School admin" or "receptionist" becomes an all-or-nothing trust bucket.
- Sensitive actions like fee collection, user management, and admissions are not protected at action level.
- This increases privilege escalation risk as features grow.

Recommended solution:
- Introduce resource policies and permission-based access control.

Suggested implementation steps:
1. Use `spatie/laravel-permission` fully or remove it.
2. Define permissions such as:
   - `students.view`
   - `students.update`
   - `fees.collect`
   - `users.manage`
   - `settings.manage`
3. Map roles to permissions in seeders.
4. Add policies for core resources:
   - Student
   - Fee
   - FeePayment
   - User
   - School settings
5. Replace broad route role checks with:
   - `can:` middleware
   - controller `authorize()`
   - policy methods

### 5. Tenant isolation depends too much on manual `school_id` filtering

Severity: High

Relevant files:
- [app/Traits/Tenantable.php](/var/projects/EduSphere/app/Traits/Tenantable.php:12)
- [app/Models/User.php](/var/projects/EduSphere/app/Models/User.php:16)
- [README.md](/var/projects/EduSphere/README.md:111)

What is happening:
- Many models use a `Tenantable` trait with a global scope.
- Some high-sensitivity areas still rely on manual `where('school_id', ...)`.
- `User` is tenant-owned but does not use the `Tenantable` trait.
- The README claims automatic global-scope isolation everywhere, which is stronger than the real implementation.

Why this matters:
- Manual scoping eventually gets missed.
- One missed query in an export, report, dashboard, or background job becomes a cross-tenant incident.

Recommended solution:
- Enforce tenant boundaries closer to the data model.

Suggested implementation steps:
1. Decide on one tenancy strategy and document it clearly:
   - row-level tenancy with mandatory global scopes, or
   - separate databases/schemas per tenant
2. If staying row-level:
   - add `Tenantable` to all tenant-owned models, including `User`
   - provide safe escape hatches only for super-admin use
   - create static analysis rules or tests for tenant-owned models missing scopes
3. Add a `BelongsToTenant` interface or base model contract.
4. Add repository/query helpers so controllers stop rebuilding manual scoping ad hoc.
5. Update docs so architecture claims match actual behavior.

### 6. Student update flow validates input but does not save it

Severity: High

Relevant file:
- [app/Http/Controllers/School/StudentController.php](/var/projects/EduSphere/app/Http/Controllers/School/StudentController.php:140)

What is happening:
- The method validates and returns success.
- It never updates the model.

Why this matters:
- This creates silent data integrity failure.
- Users may believe operational updates were applied when they were not.

Recommended solution:
- Persist the validated payload using an explicit field map.

Suggested implementation steps:
1. Fix the update method to save allowed attributes.
2. Align request field names with model attributes. Example:
   - input `phone` currently does not match `mobile_no`
3. Add a dedicated `UpdateStudentRequest`.
4. Add a feature test that verifies persisted changes in the database.

### 7. Billing and subscription logic is mostly placeholder-level

Severity: High

Relevant files:
- [app/Http/Middleware/TenantMiddleware.php](/var/projects/EduSphere/app/Http/Middleware/TenantMiddleware.php:27)
- [/.env.example](/var/projects/EduSphere/.env.example:149)

What is happening:
- Subscription enforcement exists only as date checks on the `schools` table.
- Env config advertises Stripe and webhook secrets.
- No real webhook processing, invoice state machine, retry logic, or subscription lifecycle service was found.

Why this matters:
- SaaS billing is operationally critical.
- Without webhooks and lifecycle automation, entitlement drift is likely.
- Manual billing state tends to create support burden and access-control errors.

Recommended solution:
- Either clearly document that billing is not implemented yet, or build it as a first-class subsystem.

Suggested implementation steps:
1. If using Stripe:
   - add webhook controllers
   - verify signatures
   - persist event IDs for idempotency
   - handle subscription created/updated/cancelled/past_due/unpaid events
2. Separate:
   - subscription status
   - billing status
   - feature entitlement state
3. Add a `subscriptions` table and billing event log.
4. Prevent tenant access based on entitlement state derived from billing events, not only one date column.
5. Add retry-safe webhook processing.

### 8. Documentation and implementation are out of sync

Severity: Medium

Relevant files:
- [README.md](/var/projects/EduSphere/README.md:3)
- [composer.json](/var/projects/EduSphere/composer.json:11)

What is happening:
- README says Laravel 11, Vue 3, Inertia.js.
- The codebase currently depends on Laravel 12 and uses Livewire.
- README claims automatic global-scope tenant isolation everywhere and references docs that are not present.

Why this matters:
- It slows onboarding.
- It causes wrong architectural assumptions during maintenance and audits.

Recommended solution:
- Update the README to reflect the current system honestly.

Suggested implementation steps:
1. Correct framework and frontend stack descriptions.
2. Document the real tenancy model and its limitations.
3. Document which billing features are implemented vs planned.
4. Add links to actual architecture docs or remove references to missing ones.

### 9. Tests are present but not operationally ready in this environment

Severity: Medium

Relevant files:
- [README.md](/var/projects/EduSphere/README.md:122)
- `tests/Feature/*`

What is happening:
- Tenant tests exist, which is good.
- Running them currently fails before assertions due to missing MySQL test DB connectivity.

Why this matters:
- Security-sensitive SaaS protections need easy, repeatable CI verification.
- If the test harness is hard to run, regression risk goes up quickly.

Recommended solution:
- Make the default test experience deterministic and lightweight.

Suggested implementation steps:
1. Provide a SQLite or containerized MySQL test profile.
2. Add `.env.testing.example`.
3. Ensure CI runs:
   - tenancy tests
   - auth tests
   - billing tests
   - file-access tests
4. Add negative-path tests for tenant escapes and privilege escalation.

### 10. Controller and workflow duplication increases defect risk

Severity: Medium

Examples:
- School and receptionist student registration flows
- School and receptionist admission flows
- Repeated validation logic across controllers

Why this matters:
- Fixes land in one flow and not the other.
- Security hardening must be duplicated.
- Behavior diverges over time.

Recommended solution:
- Extract shared workflows into:
  - Form Requests
  - Services
  - Action classes
  - shared response builders

Suggested implementation steps:
1. Consolidate registration/admission validation rules.
2. Consolidate upload handling into a safe shared service.
3. Move duplicated financial side effects into dedicated actions.
4. Keep role-specific differences in controllers thin.

## Recommended Remediation Roadmap

### Phase 1 - Security Containment

Target: 1 to 3 days

Actions:
1. Disable public API registration.
2. Add tenant membership checks to API auth.
3. Add `school.access` to tenant API routes.
4. Fix fee collection success/failure handling.
5. Block client-supplied raw storage path copying.

Exit criteria:
- No tenant data accessible without tenant membership
- Payment retries no longer create duplicate financial side effects from false failures
- File reuse is record-scoped, not path-scoped

### Phase 2 - Authorization And Tenancy Hardening

Target: 3 to 7 days

Actions:
1. Introduce policies and permission mapping.
2. Add `Tenantable` or equivalent enforcement to all tenant-owned models.
3. Audit every route-model-bound controller action for tenant authorization.
4. Fix broken student update flow.

Exit criteria:
- Sensitive actions are permission-gated
- Tenant boundaries are enforced at model and controller layers

### Phase 3 - Billing And Platform Reliability

Target: 1 to 2 weeks

Actions:
1. Decide whether billing is manual or automated.
2. If automated, implement Stripe webhook lifecycle handling.
3. Add subscription state tables and idempotent event processing.
4. Improve test harness and CI documentation.

Exit criteria:
- Subscription enforcement reflects real billing state
- CI can reliably validate critical SaaS behavior

## Suggested Backlog Tickets

1. Remove public API self-registration and require tenant-bound provisioning
2. Add tenant membership validation to API login and token issuance
3. Protect tenant API with `school.access` and resource permissions
4. Refactor fee payment service to return deterministic success after commit
5. Add idempotency keys to fee collection requests
6. Replace raw file-path reuse with server-side tenant-scoped file resolution
7. Introduce `UpdateStudentRequest` and persist student updates correctly
8. Adopt policies/Gates for students, fees, users, and settings
9. Add tenant scope enforcement checks for all tenant-owned models
10. Align README and architecture docs with actual codebase state
11. Add `.env.testing.example` and runnable local/CI test instructions
12. Design and implement real subscription lifecycle handling

## Notes For Maintainers

- This document focuses on the highest-signal issues found during audit sampling.
- It should be treated as a living remediation document.
- Once fixes are shipped, add:
  - link to PR
  - owner
  - target release
  - verification test

## Suggested Tracking Table

| ID | Finding | Severity | Owner | Status | Target Release |
| --- | --- | --- | --- | --- | --- |
| A-01 | API tenant boundary too weak | Critical |  | Open |  |
| A-02 | Fee collection false-failure after commit | Critical |  | Open |  |
| A-03 | Unsafe file path trust across tenants | Critical |  | Open |  |
| A-04 | Coarse role-only authorization | High |  | Open |  |
| A-05 | Tenant isolation depends on manual scoping | High |  | Open |  |
| A-06 | Student update flow does not persist | High |  | Open |  |
| A-07 | Billing lifecycle mostly placeholder-level | High |  | Open |  |
| A-08 | Docs and implementation out of sync | Medium |  | Open |  |
| A-09 | Test harness not operationally ready | Medium |  | Open |  |
| A-10 | Duplicated workflow logic | Medium |  | Open |  |
