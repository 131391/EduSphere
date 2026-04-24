Fix 4: Idempotency-Key + unique transaction_id
Fix 3: Atomic numbering sequence
Fix 2: Migrate money math to BCMath
Fix 1: Implement fees:apply-late daily command
Fix 5: Fix cascade deletes on students/fees
Fix 13: Scope api/FeeController to caller's role
Fix 9: Collapse AdmissionService fee path into FeePaymentService

# Fee Management Module — Deep Financial Audit

**Project:** EduSphere (Laravel 12 multi-tenant School ERP)
**Branch:** `main`
**Commit:** `c78a8f5`
**Auditor:** Staff-Level Software Architect / Financial Systems Audit
**Date:** 2026-04-24

**Files reviewed:** 18 controllers/services, 10 migrations, 9 models, 1 enum, 1 policy, 1 test, 1 trait, `routes/*`.

---

## 1. System Flow Summary

### Domain entities

| Entity | Purpose |
|---|---|
| `fee_types` | Master list (Tuition, Library, …) per school. |
| `fee_names` | Named fee heads under a type (e.g. "April Tuition"). |
| `fee_masters` | Class × fee_type × fee_name → amount (the template). |
| `admission_fees`, `registration_fees` | Per-class flat amounts (separate tables, **NOT linked to `fees`**). |
| `miscellaneous_fees` | Generic misc fee definitions — *no connection to any student*. |
| `late_fees` | Schema: `fine_date` (INT) + `late_fee_amount`. Standalone config. |
| `fees` | Invoice/bill issued to a student. |
| `fee_payments` | Ledger entries against a `fee`. |

### Designed flow (reverse-engineered)

1. Admin defines `fee_types` → `fee_names` → `fee_masters` (per class).
2. Admin triggers `FeeController@store` → `FeeService::generateClassFees()` which fans out `Fee` rows for every active student in the class for the selected period.
3. Receptionist opens `FeePaymentController@collect` → selects pending fees → POST → `FeePaymentService::collectPayment()` creates `FeePayment` rows and updates `fees.paid_amount`, `due_amount`, `payment_status`.
4. Admission: `AdmissionService::handleFinancialIntegration()` creates a separate `Fee` + `FeePayment` pair tagged `fee_period = 'Admission'`.
5. **Late fee: nothing happens.** The `late_fees` table can be CRUDed by admins but no cron/job/observer applies it anywhere.

---

## 2. Critical Financial Risks ⚠️

### 🔴 R1 — Late fee feature is entirely inoperative (NOT IMPLEMENTED)

- `LateFee` model and the `late_fees` table exist purely as dead configuration. Nothing reads them.
- Grep confirms: zero references to `LateFee` / `fine_date` / `late_fee` outside `LateFeeController`, the model, form requests and the `late_fee` column setter.
- `fees.late_fee` column is never written. `fees.payable_amount` is never increased past `due_date`.
- Overdue detection exists cosmetically (`Fee::scopeOverdue`) but never mutates the receivable.
- **Impact:** Every late-paying parent effectively pays **zero penalty**; school loses revenue and the finance team will see late fees "configured" but collected receipts won't reflect any. This is a **silent data-integrity gap**.

### 🔴 R2 — Financial math runs through PHP `float`

- `FeePaymentService.php` casts every amount to `floatval()` / `(float)`, mutates the model, and writes it back.
- PHP `float` has IEEE-754 binary precision; `0.1 + 0.2 !== 0.3`. After 50+ partial payments a fee can show `due_amount = -0.00000003` or `0.00000005` — the `<=0` check randomly flips between `Paid` and `Partial`.
- Compounds when `waiver`/`discount` are present (subtractions).
- In a regulated ledger this is unacceptable. **Fix:** use `bcadd/bcsub` or store in paisa (`unsignedBigInteger`).

### 🔴 R3 — Receipt number generation has a race window

`generateReceiptNumber` uses a `Cache::lock` that expires **before** the transaction commits:

```
Cache::lock(...)->block(3, function() { return max()+1; });   // lock released here
DB::commit();                                                 // INSERT happens later
```

Two concurrent cashiers:
1. Both enter the lock sequentially, both compute `RCPT-1-2026-000042` & `...-000043`.
2. Lock released after read, before insert.
3. Now transactions race; DB unique index `(school_id, receipt_no)` saves you → one request gets `IntegrityConstraintViolation` with no retry → **payment silently failed from the customer's perspective** (money taken, receipt not issued, or vice-versa depending on PSP integration).

Same bug in `generateBillNumber` and in the duplicate implementations inside `AdmissionService`.

### 🔴 R4 — No idempotency on payment collection

- No unique constraint on `fee_payments.transaction_id`.
- No idempotency key in the request.
- A double-clicked "Submit" / retried PSP callback posts **two** identical `FeePayment` rows, halving `due_amount` twice. `lockForUpdate` does not help: both requests are valid sequential writes.
- Because `min:0.01` is enforced and `currentDue` is capped, the **second** charge will silently shrink to whatever's left — so row 1 = full amount, row 2 = 0 (skipped) … **unless** overpayment is possible via concurrent lock ordering, in which case you end up with `paid_amount > payable_amount`.

### 🔴 R5 — `Fee::destroy` soft-deletes fees with payments attached

`FeeController::destroy` hard-fires `$fee->delete()` with no check for `fee_payments.fee_id = $fee->id`. A soft-deleted fee still leaves payment rows pointing at an invisible parent. Reports that `join` on `fees` will under-report collections.

### 🔴 R6 — Admission fee path is a parallel, uncontrolled mini-ledger

`AdmissionService::handleFinancialIntegration` creates `Fee` + `FeePayment` with its own (different) bill/receipt generator, **bypassing** `FeeService` and `FeePaymentService`. Two sequence families = two audit trails. Risk of gaps, duplicates, divergent bill_no format. Also sets `payment_mode = strtolower($paymentMethod->name)` which is `cash|online|cheque|bank_transfer` on the enum — silently fails if someone calls a method "UPI" (enum violation → column is nullable so the value becomes NULL, losing the mode).

---

## 3. Business Logic Bugs

### B1. `Fee::markAsPaid()` overwrites `paid_amount`

```php
public function markAsPaid($amount, $paymentMode, $transactionId = null): void
{
    $this->paid_amount = $amount;   // ❌ overwrites — prior partial lost
    $this->due_amount  = number_format($this->payable_amount - $amount - ..., 2, '.', '');
    $this->payment_status = $this->due_amount > 0 ? Partial : Paid;
```

- Dead today (service doesn't call it), but any future caller will corrupt data silently.
- Also: `number_format(..., 2, '.', '')` returns a **string**; comparison `"0.00" > 0` relies on PHP loose type coercion — flaky and also a code smell.

### B2. `due_amount` can go negative

Neither service nor schema enforces `due_amount ≥ 0`. Raising `waiver_amount` or `discount_amount` after payment produces a negative due. No clamp, no credit-note concept.

### B3. `LateFee.fine_date` semantically ambiguous

- Migration comment: *"Days after due date"*.
- Form validation: `'fine_date' => 'required|integer|min:1|max:31'` — reads like day-of-month.
- Controller transformer returns it raw. UI labeling is inconsistent across the module.
- Whoever eventually implements late-fee application will have to pick an interpretation — and the other reading silently produces wrong fines.

### B4. Fee generation uniqueness is too weak

`FeeService::generateClassFees` dedups on `(school_id, student_id, fee_name_id, fee_period)` via `exists()` check — no DB unique index, and ignores `fee_type_id` and `academic_year_id`. Two clicks in quick succession race past `exists()` → duplicate `Fee` rows. Parent is charged twice; impossible to auto-distinguish.

### B5. `fee_period` is free-text

No normalization (e.g. "April 2025" vs "Apr-2025" vs "April, 2025"). The dedup key above breaks immediately. Reports grouping by period become unreliable.

### B6. "Overdue" payment_status (`=4`) is never auto-set

Enum has `Overdue` but no code writes it. `Fee::scopeOverdue` matches either the status or a computed `due_date < now()` on pending rows — inconsistent with `FeeController::getTableStats` which only checks the date. Data and UX will drift.

### B7. Receipts aggregate payments under a single `receipt_no`, but the receipt endpoint does not verify it's *one* transaction

`FeePaymentController::receipt` renders `->with('fee.feeName', ...)->get()` — if the receipt_no was ever reused (see R3 race), parent sees someone else's payments. No defensive check.

### B8. Overpayment cap is silent

```php
if ($amountToPay > $currentDue && $currentDue > 0) {
    $amountToPay = $currentDue;  // cashier never told
}
```
Customer pays ₹5,000 on a ₹2,000 balance → response says `total_amount = 2000` with no warning. Should raise a `422` or return an explicit "adjusted" flag.

### B9. `payment_date` not validated vs `today`

`payment_date` on the store request is `required|date` only → a cashier can record a ₹1,00,000 payment dated 2099-01-01 or 1970-01-01. Reports break, tax periods break.

### B10. `payments` array has no upper bound

`'payments' => 'required|array|min:1'` — no `max`. A single request can carry 10,000 rows and hold `lockForUpdate` on every fee of the school. DoS + lock-pile.

---

## 4. Edge Case Failures

| Scenario | Current behavior | Correct behavior |
|---|---|---|
| Student pays after due date | **No penalty applied.** Fee still `Pending`. | Should auto-apply `late_fees` row before allowing partial payment. |
| Partial before + partial after due date | Same as above. Second payment ignores late fee. | Split: pre-due amount → no fine, post-due → fine. Not implemented. |
| Overpay in single tx | Capped silently, receipt shows adjusted amount. | 422 error or credit-note creation. |
| Duplicate/retried POST | Double `FeePayment` row possible (R4). | Idempotency-Key or unique `transaction_id`. |
| PSP failure mid-transaction | DB rolls back but **receipt_no was computed before `beginTransaction`** — rollback leaves a "hole" in sequence; next call may reuse (race R3). | Generate receipt_no **inside** the transaction and commit-or-throw. |
| Academic-year rollover | `due_amount` left as-is. No carry-forward, no closure entry. Student next year still sees prior-year pending. | Needs year-close / carry-forward routine. Not implemented. |
| Fee master updated after fee generated | Existing `fees.payable_amount` is frozen (good), but no warning to admin. | OK, but UI should say so. |
| Student deleted | `fees.student_id` has `ON DELETE CASCADE` → **payments and the paid history vanish.** | Change to `SET NULL` or `RESTRICT` — you can't delete a student who has paid. This is the single most dangerous cascade in the module. |
| Fee deleted | Soft-delete → `FeePayment` stays orphan (cascade is hard, but soft delete doesn't trigger). | Block deletion if payments exist. |
| Refund / reversal | **No endpoint exists.** Cashier would have to delete payment row directly (also not exposed). | Dedicated reversal entry with negative amount + approval trail. |

---

## 5. Security Issues

### S1. Registration fees / admission fees / fee generation bypass policies

- `FeeController` — `index`, `create`, `store`, `destroy`, `show` have **zero** `$this->authorize()` calls. Only `authorizeTenant` (tenant scope) is used.
- Same for `LateFeeController`, `AdmissionFeeController`, `RegistrationFeeController`.
- Only `FeePaymentController` uses a policy. **Result:** any authenticated user of the tenant — including "Student" or "Parent" roles if they land in the school layout — could potentially create/destroy class-wide fee runs. The only defender is a route-level role middleware; if that's loose, it's game over.

### S2. `FormRequest::authorize()` returns `true` everywhere

`StoreLateFeeRequest`, `UpdateLateFeeRequest` and others — `return true;` unconditionally. Relies entirely on route middleware; defense-in-depth is gone.

### S3. API `FeeController` exposes all fees of the school without per-user scoping

`api/FeeController.php` — any sanctum-authenticated token of the tenant (including a student's token) returns **all** fees of the school filtered only by school scope. No check against the caller's role. A student token = full school fee roll exported.

### S4. Mass-assignment surface on `Fee`

`fillable` includes `paid_amount`, `due_amount`, `waiver_amount`, `late_fee`, `payment_status`, `discount_amount`, `transaction_id`. There's no `update` endpoint for `Fee` today, but any future controller that does `$fee->update($request->all())` will let a user set `paid_amount = payable_amount`. Remove these fields from `$fillable` and mutate them only via the service.

### S5. Parent FeeController doesn't scope by school

`Parent/FeeController` queries `Fee::whereIn('student_id', $studentIds)` — no `school_id` filter. Relies on `Tenantable` global scope, which only activates when `currentSchool` is bound. Parent routes don't guarantee that (confirm middleware). If missed, you cross tenants.

### S6. Receipt endpoint leaks to anyone who guesses a receipt_no

`FeePaymentController::receipt($receipt_no)` → `authorize('viewAny', FeePayment::class)` passes for all finance operators, then just fetches by receipt_no. A receptionist can view any receipt of the school by iterating numeric sequences.

### S7. Negative amounts not checked at aggregate level

Validation is `min:0.01` per payment, but no check that `sum(payments.*.amount) ≤ sum(related fees due)`. Combined with R4 you can race into `paid_amount > payable_amount`.

---

## 6. Data Integrity Risks

- **Cascade deletes from `students` → `fees` → `fee_payments`**: single `Student::forceDelete` nukes a school's ledger for that student. Should be `onDelete('restrict')` for any row with payments.
- **`fee_types` cascade on `fees` / `fee_names` cascade on `fees`**: deleting a fee type deletes all historical bills. Financial records **must** be immutable once posted.
- **No unique index** on `(school_id, student_id, fee_name_id, fee_period, academic_year_id)`. Dedup relies on application-level `exists()` (race-prone, §B4).
- **No check constraints** for `payable_amount ≥ paid_amount + waiver + discount`, `due_amount ≥ 0`, `paid_amount ≥ 0`.
- **No `unique` on `transaction_id`** — reconciliation with bank statements is unsafe.
- **`fee_payments.amount` has no CHECK > 0**.
- **Soft-deletes on `fee_types`, `fee_names`, `payment_methods`**: a soft-deleted payment method is still joined via `belongsTo` (no `withTrashed` guard in receipt view) — receipts will show "N/A" if method gets deleted.

---

## 7. Performance Problems

- `FeePaymentController::handleAjaxTable`: `withCount` + `withSum` on a potentially multi-thousand-row `students` table with no composite index on `fees(school_id, student_id, payment_status)` → MySQL will likely scan-and-filter per student.
- **Student fee controller** loads all fees with `->get()` then `->filter` in PHP — for a long-enrolled student this is O(N) PHP each page load; acceptable now, will hurt as history grows.
- `Fee::where('bill_no', 'like', "{$prefix}-...-%")->max('bill_no')` — `max()` over a string cast is not optimal. Use a separate `sequences` table or DB sequence.
- `LateFeeController` `search` uses `WHERE fine_date LIKE '%x%' OR late_fee_amount LIKE '%x%'` on integer/decimal columns → full scan.
- `FeeController::handleAjaxTable`'s `whereHas('student', ...)` has an `orWhere('bill_no', 'like', ...)` **inside** the `student` relation — that means searching by bill_no returns **no rows** (it's being applied against `students` columns). Logic bug and performance sink.
- N+1 risk in `Fee::scopeOverdue` when combined with `->with()` from callers; relationships `student`, `feeName`, `class` are eager loaded in the index, good.

---

## 8. Code Quality Issues

- **Duplicated `generateReceiptNumber` / `generateBillNumber`** in `FeeService`, `FeePaymentService`, and `AdmissionService`. Classic copy-paste that already diverged. Extract to a `ReceiptNumberGenerator` service.
- **Service returns associative arrays** (`['success' => bool, 'message' => …]`) instead of throwing typed exceptions or returning Result objects. Controllers end up with `if ($result['success'])` boilerplate in many places.
- **Two shapes of the return payload** from `collectPayment`: `receipt_no` at the top level *and* inside `data.receipt_no`. The test asserts `data.receipt_no`, the controller reads top-level `receipt_no`. Cognitive overhead.
- **`Fee::class()`** relationship collides with PHP's reserved-looking keyword `class` — works, but many IDE/static analyzers choke. Rename to `classModel()` or `schoolClass()`.
- **`FeeStatus` enum compared with `!= 3` integer** in `FeeService.php` while elsewhere `!= FeeStatus::Paid`. Pick one.
- **Activity log causedBy `auth()->user()`** runs *after* `DB::commit()` inside a try/catch that swallows errors — that's good, but the activity has no reference to the `FeePayment` row, making audit queries clunky. Log `performedOn($feePayment)` and add receipt_no in properties.
- **No unit tests for `FeeService::generateClassFees`**, no tests for overdue detection, no tests for concurrency / race conditions. Only `FeePaymentServiceTest` exists and it's happy-path only.
- **`MiscellaneousFeeService`** wraps a single `->create()` in a transaction — unnecessary and misleading.
- **`FeeController::destroy` returns 500 on exception** with raw `$e->getMessage()` — leaks internal details.
- **`handleAjaxTable` returns inconsistent JSON shapes** across controllers.

---

## 9. Fixes (code suggestions)

### Fix 1 — Implement late-fee application as a daily command

```php
// app/Console/Commands/ApplyLateFees.php
namespace App\Console\Commands;

use App\Enums\FeeStatus;
use App\Models\{Fee, LateFee, School};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ApplyLateFees extends Command
{
    protected $signature   = 'fees:apply-late';
    protected $description = 'Apply configured late fees to overdue pending/partial fees.';

    public function handle(): int
    {
        School::query()->active()->chunk(50, function ($schools) {
            foreach ($schools as $school) {
                $config = LateFee::where('school_id', $school->id)
                    ->orderBy('fine_date')    // tiered
                    ->get();
                if ($config->isEmpty()) continue;

                Fee::where('school_id', $school->id)
                    ->whereIn('payment_status', [FeeStatus::Pending, FeeStatus::Partial])
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', now())
                    ->chunkById(500, function ($fees) use ($config) {
                        foreach ($fees as $fee) {
                            DB::transaction(function () use ($fee, $config) {
                                $fee = Fee::whereKey($fee->id)->lockForUpdate()->first();
                                $daysLate = now()->startOfDay()->diffInDays($fee->due_date);
                                $applicable = $config->last(fn ($c) => $c->fine_date <= $daysLate);
                                if (!$applicable) return;
                                if (bccomp($fee->late_fee ?? '0', $applicable->late_fee_amount, 2) >= 0) return;

                                $delta = bcsub($applicable->late_fee_amount, $fee->late_fee ?? '0', 2);
                                $fee->late_fee       = $applicable->late_fee_amount;
                                $fee->payable_amount = bcadd($fee->payable_amount, $delta, 2);
                                $fee->due_amount     = bcadd($fee->due_amount,     $delta, 2);
                                $fee->payment_status = FeeStatus::Overdue;
                                $fee->save();
                            });
                        }
                    });
            }
        });

        return self::SUCCESS;
    }
}
```

Register daily in `bootstrap/app.php` (Laravel 11/12 style):

```php
->withSchedule(function (Schedule $schedule) {
    $schedule->command('fees:apply-late')->dailyAt('00:15')->withoutOverlapping();
})
```

### Fix 2 — Use BCMath / integers for money

```php
// In FeePaymentService::collectPayment
$currentDue = bcsub(
    bcsub(bcsub($fee->payable_amount, $fee->paid_amount, 2), $fee->waiver_amount ?? '0', 2),
    $fee->discount_amount ?? '0',
    2
);

if (bccomp($amountToPay, $currentDue, 2) === 1) {
    throw new OverpaymentException($fee->id, $amountToPay, $currentDue); // don't cap silently
}

$fee->paid_amount = bcadd($fee->paid_amount, $amountToPay, 2);
$fee->due_amount  = bcsub($currentDue, $amountToPay, 2);
$fee->payment_status = bccomp($fee->due_amount, '0', 2) === 0 ? FeeStatus::Paid : FeeStatus::Partial;
```

### Fix 3 — Atomic sequence generation

Replace the `Cache::lock` + `max()` pattern with a real DB sequence table:

```php
// migration
Schema::create('numbering_sequences', function (Blueprint $t) {
    $t->foreignId('school_id')->constrained()->onDelete('cascade');
    $t->string('kind');       // 'receipt', 'bill'
    $t->unsignedInteger('year');
    $t->unsignedBigInteger('next_value')->default(1);
    $t->primary(['school_id', 'kind', 'year']);
});

// service
public function nextReceiptNo(int $schoolId): string
{
    return DB::transaction(function () use ($schoolId) {
        $year = (int) date('Y');
        DB::table('numbering_sequences')
            ->updateOrInsert(
                ['school_id' => $schoolId, 'kind' => 'receipt', 'year' => $year],
                ['next_value' => DB::raw('COALESCE(next_value,0)+1')]
            );
        $next = DB::table('numbering_sequences')
            ->where(['school_id' => $schoolId, 'year' => $year])
            ->where('kind','receipt')
            ->lockForUpdate()->value('next_value');
        return sprintf('RCPT-%d-%d-%06d', $schoolId, $year, $next);
    });
}
```

And call it **inside** the main transaction.

### Fix 4 — Idempotency

Add a unique index and request header:

```php
// migration
$t->string('idempotency_key', 64)->nullable();
$t->unique(['school_id', 'idempotency_key']);

// controller
$key = $request->header('Idempotency-Key') ?? Str::uuid()->toString();
$data['idempotency_key'] = $key;

// service — attempt lookup first
$existing = FeePayment::where('school_id',$school->id)
    ->where('idempotency_key', $data['idempotency_key'])->first();
if ($existing) return $this->replay($existing);
```

### Fix 5 — Harden `fillable` + add validation

```php
// Fee.php
protected $fillable = [
    'school_id','student_id','registration_id','academic_year_id',
    'fee_type_id','fee_name_id','class_id','bill_no','fee_period',
    'payable_amount','due_date','remarks',
]; // remove paid_amount, due_amount, waiver_amount, discount_amount, late_fee, payment_status, transaction_id, payment_mode
```

Mutation of ledger columns goes through explicit methods (`applyPayment`, `applyWaiver`).

### Fix 6 — Block delete when payments exist

```php
// FeeController::destroy
if ($fee->payments()->exists()) {
    abort(422, 'Cannot delete a fee with recorded payments. Reverse the payment first.');
}
```

And add the relation on `Fee`:
```php
public function payments() { return $this->hasMany(FeePayment::class); }
```

### Fix 7 — Tighten authorization

Create `FeePolicy`, wire it in each method of `FeeController`:
```php
public function store(Request $request)
{
    $this->authorize('create', Fee::class);
    ...
}
public function destroy(Fee $fee)
{
    $this->authorize('delete', $fee);
    ...
}
```

And fix every `FormRequest`:
```php
public function authorize(): bool
{
    return $this->user()?->can('create', LateFee::class) ?? false;
}
```

### Fix 8 — Validation tightening

```php
// FeePaymentController::store
$validated = $request->validate([
    'payment_date'      => 'required|date|before_or_equal:today|after:2000-01-01',
    'payments'          => 'required|array|min:1|max:50',
    'payments.*.amount' => 'required|numeric|min:0.01|max:10000000',
    ...
]);
```

Also prevent posting against an already-paid fee:
```php
'payments.*.fee_id' => [
    'required',
    Rule::exists('fees', 'id')->where(fn($q) =>
        $q->where('school_id', $this->getSchoolId())
          ->where('student_id', $student->id)
          ->where('payment_status', '!=', FeeStatus::Paid->value)
    ),
],
```

### Fix 9 — Consolidate the admission-fee code path

Replace the inline `Fee::create()` + `FeePayment::create()` in `AdmissionService::handleFinancialIntegration` with a call to `FeePaymentService::collectPayment()`, feeding a synthetic `payments[]` for the freshly-created `Fee`. One ledger, one numbering sequence.

### Fix 10 — Dedup index on generation

```php
// new migration
Schema::table('fees', function (Blueprint $t) {
    $t->unique(
        ['school_id','student_id','academic_year_id','fee_type_id','fee_name_id','fee_period'],
        'uq_fees_student_period'
    );
});
```
And convert the `fee_period` from free text into a canonical `'YYYY-MM'` or FK to a `fee_periods` table.

---

## 10. Final Verdict

### 🚫 NOT PRODUCTION READY for a real school handling money.

**Green:** clean service layer, Tenantable scope, soft-deletes, activity log, reasonable indexes, unit test coverage for the happy path, `lockForUpdate` in place during collection, unique `(school_id, receipt_no)` constraint.

**Red (blockers):**

1. Late-fee logic does not exist (§R1).
2. Financial math on `float` (§R2).
3. Receipt/bill sequence race (§R3).
4. No idempotency / reversal / refund (§R4, §4 "Refund" row).
5. Cascade-delete of paid history via `students` (§6).
6. Fee generation authorization missing (§S1), mass-assignable ledger columns (§S4).
7. `Fee::markAsPaid` is a land mine (§B1).
8. Admission fee path forks the ledger (§R6).

Fix the 8 red items above — ideally **behind a staged rollout with reconciliation against existing data** — before any paying school touches this module. The greens are a solid foundation; the reds are table-stakes for financial software.

---

## Appendix — Prioritised Action Checklist

| # | Item | Severity | Effort |
|---|---|---|---|
| 1 | Implement `fees:apply-late` daily command | 🔴 Critical | M |
| 2 | Migrate money math to BCMath / integer paisa | 🔴 Critical | L |
| 3 | Atomic numbering sequence (receipt / bill) | 🔴 Critical | M |
| 4 | Idempotency-Key on payment collection + unique `transaction_id` | 🔴 Critical | S |
| 5 | Change `students → fees → fee_payments` cascades to `restrict` / `set null` | 🔴 Critical | S |
| 6 | Remove ledger columns from `Fee::$fillable` | 🟠 High | S |
| 7 | Add `FeePolicy` + wire `authorize()` in every fee controller | 🟠 High | M |
| 8 | Block `Fee` delete when payments exist | 🟠 High | S |
| 9 | Collapse `AdmissionService` fee path into `FeePaymentService` | 🟠 High | M |
| 10 | Add composite unique index on `fees(school_id,student_id,academic_year_id,fee_type_id,fee_name_id,fee_period)` | 🟠 High | S |
| 11 | Tighten request validation (`payment_date`, `payments.max`, enum check for fee_id) | 🟡 Medium | S |
| 12 | Remove `Fee::markAsPaid()` (dead + buggy) | 🟡 Medium | XS |
| 13 | Per-role scoping in `api/FeeController` (students shouldn't see school-wide list) | 🟡 Medium | S |
| 14 | Replace `FormRequest::authorize()=true` with real checks | 🟡 Medium | S |
| 15 | Standardise service return contract (Result object / exceptions) | 🟢 Low | M |
| 16 | Tests: concurrency, overpayment, late-fee apply, year rollover | 🟢 Low | L |
