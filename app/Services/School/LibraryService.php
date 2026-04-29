<?php

namespace App\Services\School;

use App\Models\Book;
use App\Models\BookIssue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LibraryService
{
    public function issueBook(array $data)
    {
        return DB::transaction(function () use ($data) {
            $schoolId = (int) $data['school_id'];
            $bookId   = (int) $data['book_id'];

            $book = Book::query()
                ->where('school_id', $schoolId)
                ->whereKey($bookId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($book->available_quantity > $book->quantity) {
                throw new RuntimeException('Book inventory is inconsistent. Please review available stock levels.');
            }

            if ($book->available_quantity <= 0) {
                return ['success' => false, 'message' => 'Book not available in stock.'];
            }

            // Prevent same student/staff holding multiple active copies of the same book.
            // The lockForUpdate() forces concurrent issuers to serialize on this read,
            // closing the TOCTOU window between the existence check and the INSERT.
            if (!empty($data['student_id'])) {
                $alreadyHeld = BookIssue::where('school_id', $schoolId)
                    ->where('book_id', $bookId)
                    ->where('student_id', (int) $data['student_id'])
                    ->where('status', 'issued')
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyHeld) {
                    return ['success' => false, 'message' => 'This student already has an active issue for this book.'];
                }
            }

            if (!empty($data['staff_id'])) {
                $alreadyHeld = BookIssue::where('school_id', $schoolId)
                    ->where('book_id', $bookId)
                    ->where('staff_id', (int) $data['staff_id'])
                    ->where('status', 'issued')
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyHeld) {
                    return ['success' => false, 'message' => 'This staff member already has an active issue for this book.'];
                }
            }

            // Per-borrower cap on simultaneous active issues, configurable per school.
            $schoolSettings = $book->school?->settings ?? [];
            $maxBooks       = (int) ($schoolSettings['library_max_books_per_borrower'] ?? 0);
            if ($maxBooks > 0) {
                $borrowerColumn = !empty($data['student_id']) ? 'student_id' : 'staff_id';
                $borrowerId     = !empty($data['student_id']) ? (int) $data['student_id'] : (int) ($data['staff_id'] ?? 0);
                $activeCount    = BookIssue::where('school_id', $schoolId)
                    ->where($borrowerColumn, $borrowerId)
                    ->where('status', 'issued')
                    ->count();

                if ($activeCount >= $maxBooks) {
                    return [
                        'success' => false,
                        'message' => "Borrower has reached the maximum of {$maxBooks} active issues. Process a return before issuing more.",
                    ];
                }
            }

            $issue = BookIssue::create([
                'school_id'  => $schoolId,
                'book_id'    => $bookId,
                'student_id' => isset($data['student_id']) ? (int) $data['student_id'] : null,
                'staff_id'   => isset($data['staff_id']) ? (int) $data['staff_id'] : null,
                'issue_date' => $data['issue_date'] ?? now(),
                'due_date'   => $data['due_date'],
                'status'     => 'issued',
            ]);

            $book->available_quantity -= 1;
            $book->save();

            activity('library')
                ->causedBy(auth()->user())
                ->performedOn($issue)
                ->withProperties(['book_id' => $book->id, 'student_id' => $data['student_id'] ?? null])
                ->log('book_issued');

            return ['success' => true, 'message' => 'Book issued successfully.', 'issue' => $issue];
        });
    }

    public function returnBook(BookIssue $issue, $returnDate = null)
    {
        return DB::transaction(function () use ($issue, $returnDate) {
            $lockedIssue = BookIssue::query()
                ->where('school_id', (int) $issue->school_id)
                ->whereKey($issue->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedIssue->status !== 'issued') {
                return ['success' => false, 'message' => 'This book is already marked as ' . $lockedIssue->status];
            }

            $book = Book::query()
                ->where('school_id', (int) $lockedIssue->school_id)
                ->whereKey((int) $lockedIssue->book_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($book->available_quantity >= $book->quantity) {
                return [
                    'success' => false,
                    'message' => 'Book inventory is already fully available. Please review this issue record before retrying.',
                ];
            }

            $returnDate = $returnDate ? Carbon::parse($returnDate)->startOfDay() : now()->startOfDay();
            $lockedIssue->return_date = $returnDate;
            $lockedIssue->fine_amount = 0;

            if ($returnDate->gt(Carbon::parse($lockedIssue->due_date))) {
                $daysOverdue    = (int) Carbon::parse($lockedIssue->due_date)->startOfDay()->diffInDays($returnDate->copy()->startOfDay());
                $schoolSettings = $lockedIssue->school?->settings ?? [];
                $finePerDay     = (float) ($schoolSettings['late_return_library_book_fine'] ?? 5);
                $lockedIssue->fine_amount = round($daysOverdue * $finePerDay, 2);
            }

            $lockedIssue->status = 'returned';
            $lockedIssue->save();

            $book->available_quantity += 1;
            $book->save();

            activity('library')
                ->causedBy(auth()->user())
                ->performedOn($lockedIssue)
                ->withProperties(['fine' => (float) $lockedIssue->fine_amount])
                ->log('book_returned');

            return ['success' => true, 'message' => 'Book returned successfully.', 'fine' => number_format((float) $lockedIssue->fine_amount, 2, '.', '')];
        });
    }

    public function markAsLost(BookIssue $issue)
    {
        return DB::transaction(function () use ($issue) {
            $lockedIssue = BookIssue::query()
                ->where('school_id', (int) $issue->school_id)
                ->whereKey($issue->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedIssue->status !== 'issued') {
                return ['success' => false, 'message' => 'This book is already marked as ' . $lockedIssue->status];
            }

            $book = Book::query()
                ->where('school_id', (int) $lockedIssue->school_id)
                ->whereKey((int) $lockedIssue->book_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($book->quantity <= 0) {
                throw new RuntimeException('Book quantity is already zero and cannot be decremented.');
            }

            $lockedIssue->status      = 'lost';
            $lockedIssue->return_date = now()->toDateString();
            $lockedIssue->fine_amount = $book->price ?? 0;
            $lockedIssue->save();

            $book->quantity -= 1;
            if ($book->available_quantity > $book->quantity) {
                $book->available_quantity = $book->quantity;
            }
            $book->save();

            activity('library')
                ->causedBy(auth()->user())
                ->performedOn($lockedIssue)
                ->withProperties(['fine' => (float) $lockedIssue->fine_amount])
                ->log('book_lost');

            return ['success' => true, 'message' => 'Book marked as lost. Fine applied.'];
        });
    }

    public function settleFine(BookIssue $issue, array $details = [])
    {
        return DB::transaction(function () use ($issue, $details) {
            $locked = BookIssue::query()
                ->where('school_id', (int) $issue->school_id)
                ->whereKey($issue->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === 'issued') {
                return ['success' => false, 'message' => 'Book has not been returned or marked lost yet.'];
            }

            if ($locked->fine_paid_at !== null) {
                return ['success' => false, 'message' => 'Fine has already been settled.'];
            }

            if ((float) $locked->fine_amount <= 0) {
                return ['success' => false, 'message' => 'No outstanding fine on this record.'];
            }

            $paidAmount = isset($details['paid_amount'])
                ? round((float) $details['paid_amount'], 2)
                : round((float) $locked->fine_amount, 2);

            if ($paidAmount <= 0) {
                return ['success' => false, 'message' => 'Settlement amount must be greater than zero.'];
            }
            if ($paidAmount > (float) $locked->fine_amount) {
                return ['success' => false, 'message' => 'Settlement amount cannot exceed the outstanding fine.'];
            }

            $locked->fine_paid_at         = now();
            $locked->fine_paid_amount     = $paidAmount;
            $locked->fine_payment_method  = $details['payment_method'] ?? 'cash';
            $locked->fine_collected_by    = auth()->id();
            $locked->fine_settlement_note = $details['note'] ?? null;
            $locked->save();

            activity('library')
                ->causedBy(auth()->user())
                ->performedOn($locked)
                ->withProperties([
                    'fine'           => (float) $locked->fine_amount,
                    'paid_amount'    => $paidAmount,
                    'payment_method' => $locked->fine_payment_method,
                ])
                ->log('fine_settled');

            return [
                'success'        => true,
                'message'        => 'Fine settled successfully.',
                'fine'           => (float) $locked->fine_amount,
                'paid_amount'    => $paidAmount,
                'payment_method' => $locked->fine_payment_method,
            ];
        });
    }

    public function renewBook(BookIssue $issue, $newDueDate)
    {
        return DB::transaction(function () use ($issue, $newDueDate) {
            $lockedIssue = BookIssue::query()
                ->where('school_id', (int) $issue->school_id)
                ->whereKey($issue->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedIssue->status !== 'issued') {
                return ['success' => false, 'message' => 'Only issued books can be renewed.'];
            }

            // Reject renewal of already-overdue issues — borrower must return + pay
            // the fine first, otherwise renewal becomes a fine-evasion mechanism.
            if (Carbon::parse($lockedIssue->due_date)->startOfDay()->isPast()) {
                return [
                    'success' => false,
                    'message' => 'This issue is already overdue. Please process the return and settle any fine before renewing.',
                ];
            }

            // Cap the number of renewals per issue, per school setting.
            $schoolSettings = $lockedIssue->school?->settings ?? [];
            $maxRenewals    = $schoolSettings['library_max_renewals'] ?? null;
            if ($maxRenewals !== null && $maxRenewals !== '' && (int) $lockedIssue->renewal_count >= (int) $maxRenewals) {
                return [
                    'success' => false,
                    'message' => "Maximum number of renewals ({$maxRenewals}) reached for this issue.",
                ];
            }

            $oldDueDate = $lockedIssue->due_date;
            $lockedIssue->due_date       = Carbon::parse($newDueDate);
            $lockedIssue->renewal_count = (int) $lockedIssue->renewal_count + 1;
            $lockedIssue->save();

            activity('library')
                ->causedBy(auth()->user())
                ->performedOn($lockedIssue)
                ->withProperties([
                    'old_due_date'   => Carbon::parse($oldDueDate)->toDateString(),
                    'new_due_date'   => Carbon::parse($lockedIssue->due_date)->toDateString(),
                    'renewal_count'  => (int) $lockedIssue->renewal_count,
                ])
                ->log('book_renewed');

            return ['success' => true, 'message' => 'Book renewal successful. New due date: ' . Carbon::parse($lockedIssue->due_date)->format('d M, Y')];
        });
    }

    /**
     * Recover a previously-lost book. Restores quantity and (if the title is
     * still cataloged) available_quantity, voids the fine if it has not yet
     * been paid, and flips the issue status to 'returned' for audit clarity.
     */
    public function recoverLostBook(BookIssue $issue)
    {
        return DB::transaction(function () use ($issue) {
            $locked = BookIssue::query()
                ->where('school_id', (int) $issue->school_id)
                ->whereKey($issue->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'lost') {
                return ['success' => false, 'message' => 'Only books currently marked as lost can be recovered.'];
            }

            $book = Book::query()
                ->where('school_id', (int) $locked->school_id)
                ->whereKey((int) $locked->book_id)
                ->lockForUpdate()
                ->first();

            if ($book) {
                $book->quantity           = (int) $book->quantity + 1;
                $book->available_quantity = (int) $book->available_quantity + 1;
                $book->save();
            }

            $voidedFine = $locked->fine_paid_at === null && (float) $locked->fine_amount > 0;
            if ($voidedFine) {
                $locked->fine_amount = 0;
            }
            $locked->status      = 'returned';
            $locked->return_date = $locked->return_date ?? now()->toDateString();
            $locked->save();

            activity('library')
                ->causedBy(auth()->user())
                ->performedOn($locked)
                ->withProperties([
                    'restored_quantity' => $book?->quantity,
                    'voided_fine'       => $voidedFine,
                ])
                ->log('book_recovered');

            return [
                'success' => true,
                'message' => $voidedFine
                    ? 'Book recovered. Inventory restored and unpaid fine voided.'
                    : 'Book recovered. Inventory restored.',
            ];
        });
    }

    /**
     * Adjust the total stock of a book by a positive (purchase / donation) or
     * negative (damage / shrinkage / cull) delta. Available quantity tracks
     * along with it so issued copies are never accidentally removed.
     */
    public function adjustStock(Book $book, int $delta, string $reason)
    {
        return DB::transaction(function () use ($book, $delta, $reason) {
            $locked = Book::query()
                ->where('school_id', (int) $book->school_id)
                ->whereKey($book->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $newQuantity = (int) $locked->quantity + $delta;
            if ($newQuantity < 0) {
                return ['success' => false, 'message' => 'Stock adjustment would make total quantity negative.'];
            }

            $issuedOut = (int) $locked->quantity - (int) $locked->available_quantity;
            if ($newQuantity < $issuedOut) {
                return [
                    'success' => false,
                    'message' => "Cannot reduce stock below the number of copies currently issued ({$issuedOut}).",
                ];
            }

            $locked->quantity           = $newQuantity;
            $locked->available_quantity = max(0, $newQuantity - $issuedOut);
            $locked->save();

            activity('library')
                ->causedBy(auth()->user())
                ->performedOn($locked)
                ->withProperties([
                    'delta'              => $delta,
                    'reason'             => $reason,
                    'new_quantity'       => $newQuantity,
                    'available_quantity' => (int) $locked->available_quantity,
                ])
                ->log('stock_adjusted');

            return [
                'success'            => true,
                'message'            => "Stock adjusted by {$delta}. New total: {$newQuantity}.",
                'quantity'           => $newQuantity,
                'available_quantity' => (int) $locked->available_quantity,
            ];
        });
    }
}
