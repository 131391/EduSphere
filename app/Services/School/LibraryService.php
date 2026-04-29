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

            // Prevent same student holding multiple active copies of the same book
            if (!empty($data['student_id'])) {
                $studentId   = (int) $data['student_id'];
                $alreadyHeld = BookIssue::where('school_id', $schoolId)
                    ->where('book_id', $bookId)
                    ->where('student_id', $studentId)
                    ->where('status', 'issued')
                    ->exists();

                if ($alreadyHeld) {
                    return ['success' => false, 'message' => 'This student already has an active issue for this book.'];
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

    public function settleFine(BookIssue $issue)
    {
        return DB::transaction(function () use ($issue) {
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

            $locked->fine_paid_at = now();
            $locked->save();

            activity('library')
                ->causedBy(auth()->user())
                ->performedOn($locked)
                ->withProperties(['fine' => (float) $locked->fine_amount])
                ->log('fine_settled');

            return ['success' => true, 'message' => 'Fine settled successfully.', 'fine' => (float) $locked->fine_amount];
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

            $oldDueDate = $lockedIssue->due_date;
            $lockedIssue->due_date = Carbon::parse($newDueDate);
            $lockedIssue->save();

            activity('library')
                ->causedBy(auth()->user())
                ->performedOn($lockedIssue)
                ->withProperties([
                    'old_due_date' => Carbon::parse($oldDueDate)->toDateString(),
                    'new_due_date' => Carbon::parse($lockedIssue->due_date)->toDateString()
                ])
                ->log('book_renewed');

            return ['success' => true, 'message' => 'Book renewal successful. New due date: ' . Carbon::parse($lockedIssue->due_date)->format('d M, Y')];
        });
    }
}
