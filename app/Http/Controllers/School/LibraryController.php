<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookIssue;
use App\Models\Student;
use App\Services\School\LibraryService;
use Illuminate\Http\Request;

class LibraryController extends TenantController
{
    protected $libraryService;

    public function __construct(LibraryService $libraryService)
    {
        parent::__construct();
        $this->libraryService = $libraryService;
    }

    public function index()
    {
        $this->ensureSchoolActive();
        $books = Book::where('school_id', $this->getSchoolId())
            ->with('category')
            ->latest()
            ->paginate(15);
        
        $categories = BookCategory::where('school_id', $this->getSchoolId())->withCount('books')->get();

        return view('school.library.index', compact('books', 'categories'));
    }

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

        try {
            $book = Book::create([
                'school_id' => $this->getSchoolId(),
                'title' => $request->title,
                'author' => $request->author,
                'isbn' => $request->isbn,
                'category_id' => $request->category_id,
                'quantity' => $request->quantity,
                'available_quantity' => $request->quantity,
                'price' => $request->price,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Book added to catalog successfully!',
                    'data' => $book->load('category')
                ]);
            }

            return back()->with('success', 'Book added to catalog.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add book: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to add book: ' . $e->getMessage());
        }
    }

    public function issues()
    {
        $this->ensureSchoolActive();
        $activeIssues = BookIssue::where('school_id', $this->getSchoolId())
            ->where('status', 'issued')
            ->with(['book', 'student'])
            ->latest()
            ->paginate(15);

        $books = Book::where('school_id', $this->getSchoolId())
            ->where('available_quantity', '>', 0)
            ->get();
            
        $students = Student::where('school_id', $this->getSchoolId())->active()->get();

        return view('school.library.issues', compact('activeIssues', 'books', 'students'));
    }

    public function issueBook(Request $request)
    {
        $this->ensureSchoolActive();
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'student_id' => 'required|exists:students,id',
            'due_date' => 'required|date|after:today',
        ]);

        try {
            $data = $request->all();
            $data['school_id'] = $this->getSchoolId();

            $result = $this->libraryService->issueBook($data);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'data' => $result['success'] ? $result['issue']->load(['book', 'student']) : null
                ], $result['success'] ? 200 : 422);
            }

            return $result['success'] 
                ? back()->with('success', $result['message']) 
                : back()->with('error', $result['message']);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to issue book: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to issue book: ' . $e->getMessage());
        }
    }

    public function returnBook(BookIssue $issue)
    {
        try {
            $result = $this->libraryService->returnBook($issue);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'fine' => $result['success'] ? $result['fine'] : 0
                ], $result['success'] ? 200 : 422);
            }

            return $result['success'] 
                ? back()->with('success', $result['message']) 
                : back()->with('error', $result['message']);
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process return: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to process return: ' . $e->getMessage());
        }
    }
}
