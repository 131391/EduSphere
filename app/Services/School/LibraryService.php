<?php

namespace App\Services\School;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LibraryService
{
    /**
     * Issue a book to a student or staff
     */
    public function issueBook(array $data)
    {
        return DB::transaction(function () use ($data) {
            $book = Book::where('school_id', $data['school_id'])->findOrFail($data['book_id']);

            if ($book->available_quantity <= 0) {
                return ['success' => false, 'message' => 'Book not available in stock.'];
            }

            $issue = BookIssue::create([
                'school_id' => $data['school_id'],
                'book_id' => $data['book_id'],
                'student_id' => $data['student_id'] ?? null,
                'staff_id' => $data['staff_id'] ?? null,
                'issue_date' => $data['issue_date'] ?? now(),
                'due_date' => $data['due_date'],
                'status' => 'issued',
            ]);

            $book->decrement('available_quantity');

            return ['success' => true, 'message' => 'Book issued successfully.', 'issue' => $issue];
        });
    }

    /**
     * Return an issued book
     */
    public function returnBook(BookIssue $issue, $returnDate = null)
    {
        return DB::transaction(function () use ($issue, $returnDate) {
            if ($issue->status !== 'issued') {
                return ['success' => false, 'message' => 'This book is already marked as ' . $issue->status];
            }

            $returnDate = $returnDate ? Carbon::parse($returnDate) : now();
            $issue->return_date = $returnDate;

            // Calculate fine if overdue — rate is configurable via school settings
            if ($returnDate->gt($issue->due_date)) {
                $daysOverdue = $returnDate->diffInDays($issue->due_date);
                $school = \App\Models\School::find($issue->school_id);
                $finePerDay = $school?->settings['library_fine_per_day'] ?? 5;
                $issue->fine_amount = $daysOverdue * $finePerDay;
            }

            $issue->status = 'returned';
            $issue->save();

            $issue->book->increment('available_quantity');

            return ['success' => true, 'message' => 'Book returned successfully.', 'fine' => $issue->fine_amount];
        });
    }

    /**
     * Mark a book as lost
     */
    public function markAsLost(BookIssue $issue)
    {
        return DB::transaction(function () use ($issue) {
            $issue->status = 'lost';
            $issue->fine_amount = $issue->book->price ?? 0;
            $issue->save();

            // Atomically decrement only if quantity > 0
            $decremented = \App\Models\Book::where('id', $issue->book_id)
                ->where('quantity', '>', 0)
                ->decrement('quantity');

            if (!$decremented) {
                throw new \Exception('Book quantity is already zero and cannot be decremented.');
            }

            return ['success' => true, 'message' => 'Book marked as lost. Fine applied.'];
        });
    }
}
