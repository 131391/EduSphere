<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookIssue;
use App\Models\Student;
use App\Services\School\LibraryService;
use Illuminate\Http\Request;

use App\Traits\HasAjaxDataTable;

class LibraryController extends TenantController
{
    use HasAjaxDataTable;

    protected $libraryService;

    public function __construct(LibraryService $libraryService)
    {
        parent::__construct();
        $this->libraryService = $libraryService;
    }

    public function index(Request $request)
    {
        $this->ensureSchoolActive();

        $query = Book::where('school_id', $this->getSchoolId())
            ->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'title');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        
        if (in_array($sort, ['title', 'author', 'quantity', 'available_quantity', 'price'], true)) {
            $query->orderBy($sort, $direction);
        }

        $transformer = function($row) {
            return [
                'id' => $row->id,
                'title' => $row->title,
                'author' => $row->author,
                'isbn' => $row->isbn ?? 'N/A',
                'category_name' => $row->category?->name ?? 'Uncategorized',
                'total_quantity' => $row->quantity,
                'available_quantity' => $row->available_quantity,
                'status_color' => $row->available_quantity > 0 ? 'emerald' : 'rose',
                'price' => number_format((float)($row->price ?? 0), 2),
            ];
        };

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $this->getTableStats());
        }

        $hydrationData = $this->getHydrationData($query, $transformer, [
            'stats' => $this->getTableStats()
        ]);

        return view('school.library.index', array_merge($hydrationData, [
            'categories' => BookCategory::where('school_id', $this->getSchoolId())->withCount('books')->get()
        ]));
    }

    public function issues(Request $request)
    {
        $this->ensureSchoolActive();

        $query = BookIssue::where('school_id', $this->getSchoolId())
            ->where('status', 'issued')
            ->with(['book', 'student']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            })->orWhereHas('book', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'due_date');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, ['issue_date', 'due_date', 'id'], true)) {
            $query->orderBy($sort, $direction);
        }

        $transformer = function($row) {
            $isOverdue = now()->greaterThan($row->due_date);
            return [
                'id' => $row->id,
                'book_title' => $row->book?->title ?? 'N/A',
                'student_name' => $row->student?->full_name ?? 'N/A',
                'admission_no' => $row->student?->admission_no ?? 'N/A',
                'issue_date' => $row->issue_date->format('d M, Y'),
                'due_date' => $row->due_date->format('d M, Y'),
                'overdue' => $isOverdue,
                'overdue_days' => $isOverdue ? now()->diffInDays($row->due_date) : 0,
            ];
        };

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $this->getTableStats());
        }

        $hydrationData = $this->getHydrationData($query, $transformer, [
            'stats' => $this->getTableStats()
        ]);

        return view('school.library.issues', array_merge($hydrationData, [
            'books' => Book::where('school_id', $this->getSchoolId())->where('available_quantity', '>', 0)->get(),
            'students' => Student::where('school_id', $this->getSchoolId())->active()->get()
        ]));
    }

    protected function getTableStats()
    {
        return [
            'total_books' => Book::where('school_id', $this->getSchoolId())->sum('quantity'),
            'issued_books' => BookIssue::where('school_id', $this->getSchoolId())->where('status', 'issued')->count(),
            'available_titles' => Book::where('school_id', $this->getSchoolId())->count(),
            'overdue_returns' => BookIssue::where('school_id', $this->getSchoolId())
                ->where('status', 'issued')
                ->where('due_date', '<', now())
                ->count()
        ];
    }

    public function storeBook(Request $request)
    {
        $this->ensureSchoolActive();
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'category_id' => ['required', \Illuminate\Validation\Rule::exists('book_categories', 'id')->where('school_id', $this->getSchoolId())],
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

    public function issueBook(Request $request)
    {
        $this->ensureSchoolActive();
        $request->validate([
            'book_id'    => ['required', \Illuminate\Validation\Rule::exists('books', 'id')->where('school_id', $this->getSchoolId())],
            'student_id' => ['required', \Illuminate\Validation\Rule::exists('students', 'id')->where('school_id', $this->getSchoolId())],
            'due_date'   => 'required|date|after:today',
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
        $this->authorizeTenant($issue);
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
