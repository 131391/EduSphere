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
            $book = Book::findOrFail($data['book_id']);

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
            
            // Calculate fine if overdue
            if ($returnDate->gt($issue->due_date)) {
                $daysOverdue = $returnDate->diffInDays($issue->due_date);
                // Implementation assumption: 5 units per day fine. Can be made dynamic later.
                $issue->fine_amount = $daysOverdue * 5; 
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
            // Fine could be the price of the book
            $issue->fine_amount = $issue->book->price ?? 0;
            $issue->save();

            // Book quantity is not incremented back because it's lost
            // We should decrement the total quantity of the book
            $issue->book->decrement('quantity');

            return ['success' => true, 'message' => 'Book marked as lost. Fine applied.'];
        });
    }
}
