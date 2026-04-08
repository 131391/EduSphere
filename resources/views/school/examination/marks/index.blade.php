<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TenantController;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookIssue;
use App\Models\Student;
use App\Models\Staff;
use App\Services\School\LibraryService;
use Illuminate\Http\Request;

class LibraryController extends TenantController
{
    protected $libraryService;

    public function __construct(LibraryService $libraryService)
    {
        $this->libraryService = $libraryService;
    }

    /**
     * Display a listing of books
     */
    public function index()
    {
        $this->ensureSchoolActive();
        $books = Book::where('school_id', $this->getSchoolId())
            ->with('category')
            ->latest()
            ->paginate(10);
        
        $categories = BookCategory::where('school_id', $this->getSchoolId())->get();

        return view('school.library.index', compact('books', 'categories'));
    }

    /**
     * Store a new book
     */
    public function storeBook(Request $request)
    {
        $this->ensureSchoolActive();
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'category_id' => 'required|exists:book_categories,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'nullable|numeric|min:0',
        ]);

        Book::create([
            'school_id' => $this->getSchoolId(),
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'category_id' => $request->category_id,
            'quantity' => $request->quantity,
            'available_quantity' => $request->quantity,
            'price' => $request->price,
        ]);

        return back()->with('success', 'Book added to catalog.');
    }

    /**
     * Display issue form and active issues
     */
    public function issues()
    {
        $this->ensureSchoolActive();
        $activeIssues = BookIssue::where('school_id', $this->getSchoolId())
            ->where('status', 'issued')
            ->with(['book', 'student', 'staff'])
            ->latest()
            ->paginate(10);

        $books = Book::where('school_id', $this->getSchoolId())
            ->where('available_quantity', '>', 0)
            ->get();
            
        $students = Student::where('school_id', $this->getSchoolId())->active()->get();

        return view('school.library.issues', compact('activeIssues', 'books', 'students'));
    }

    /**
     * Issue a book
     */
    public function issueBook(Request $request)
    {
        $this->ensureSchoolActive();
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'student_id' => 'required|exists:students,id',
            'due_date' => 'required|date|after:today',
        ]);

        $data = $request->all();
        $data['school_id'] = $this->getSchoolId();

        $result = $this->libraryService.issueBook($data);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Return a book
     */
    public function returnBook(BookIssue $issue)
    {
        $this->authorizeTenant($issue);
        $result = $this->libraryService->returnBook($issue);

        if ($result['success']) {
            $msg = $result['message'];
            if ($result['fine'] > 0) {
                $msg .= " Fine of " . number_format($result['fine'], 2) . " applied.";
            }
            return back()->with('success', $msg);
        }

        return back()->with('error', $result['message']);
    }
}
