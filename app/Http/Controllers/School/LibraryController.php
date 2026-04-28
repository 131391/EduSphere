<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookIssue;
use App\Models\Student;
use App\Services\School\LibraryService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Traits\HasAjaxDataTable;

class LibraryController extends TenantController
{
    use HasAjaxDataTable;

    public function __construct(protected LibraryService $libraryService)
    {
        parent::__construct();
    }

    // -------------------------------------------------------------------------
    // Catalog
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        Gate::authorize('manage', Book::class);

        $query = Book::where('school_id', $this->getSchoolId())->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(fn($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('author', 'like', "%{$search}%")
                ->orWhere('isbn', 'like', "%{$search}%"));
        }

        $sort      = $request->input('sort', 'title');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (in_array($sort, ['title', 'author', 'quantity', 'available_quantity', 'price'], true)) {
            $query->orderBy($sort, $direction);
        }

        $currency    = $this->getSchool()->settings['currency_symbol'] ?? '₹';
        $transformer = fn($row) => [
            'id'                 => $row->id,
            'title'              => $row->title,
            'author'             => $row->author,
            'isbn'               => $row->isbn ?? 'N/A',
            'category_name'      => $row->category?->name ?? 'Uncategorized',
            'total_quantity'     => $row->quantity,
            'available_quantity' => $row->available_quantity,
            'status_color'       => $row->available_quantity > 0 ? 'emerald' : 'rose',
            'price'              => number_format((float) ($row->price ?? 0), 2),
            'currency'           => $currency,
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $this->getTableStats());
        }

        $initialData = $this->getHydrationData($query, $transformer, ['stats' => $this->getTableStats()]);

        return view('school.library.index', [
            'initialData'    => $initialData,
            'stats'          => $initialData['stats'],
            'currency'       => $currency,
            'categories'     => BookCategory::where('school_id', $this->getSchoolId())
                ->withCount('books')->orderBy('name')->get(),
        ]);
    }

    public function storeBook(Request $request)
    {
        $this->ensureSchoolActive();
        Gate::authorize('manage', Book::class);

        $request->validate([
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:255',
            'isbn'        => ['nullable', 'string', 'max:255',
                Rule::unique('books', 'isbn')->where('school_id', $this->getSchoolId())->whereNull('deleted_at')],
            'category_id' => ['required', Rule::exists('book_categories', 'id')->where('school_id', $this->getSchoolId())],
            'quantity'    => 'required|integer|min:1',
            'price'       => 'nullable|numeric|min:0',
        ]);

        try {
            $book = Book::create([
                'school_id'          => $this->getSchoolId(),
                'title'              => $request->title,
                'author'             => $request->author,
                'isbn'               => $request->isbn,
                'category_id'        => $request->category_id,
                'quantity'           => $request->quantity,
                'available_quantity' => $request->quantity,
                'price'              => $request->price,
            ]);

            $this->bustStatsCache();

            return $request->wantsJson()
                ? response()->json(['success' => true, 'message' => 'Book added to catalog successfully!', 'data' => $book->load('category')])
                : back()->with('success', 'Book added to catalog.');
        } catch (UniqueConstraintViolationException) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'A book with this ISBN already exists in your catalog.'], 422)
                : back()->with('error', 'A book with this ISBN already exists in your catalog.');
        } catch (\Exception $e) {
            Log::error('Failed to add library book.', ['school_id' => $this->getSchoolId(), 'exception' => $e]);
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Failed to add book. Please try again.'], 500)
                : back()->with('error', 'Failed to add book. Please try again.');
        }
    }

    public function updateBook(Request $request, Book $book)
    {
        $this->ensureSchoolActive();
        $this->authorizeTenant($book);
        Gate::authorize('manage', Book::class);

        $request->validate([
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:255',
            'isbn'        => ['nullable', 'string', 'max:255',
                Rule::unique('books', 'isbn')->where('school_id', $this->getSchoolId())->whereNull('deleted_at')->ignore($book->id)],
            'category_id' => ['required', Rule::exists('book_categories', 'id')->where('school_id', $this->getSchoolId())],
            'price'       => 'nullable|numeric|min:0',
        ]);

        try {
            $book->update($request->only('title', 'author', 'isbn', 'category_id', 'price'));
            $this->bustStatsCache();

            return $request->wantsJson()
                ? response()->json(['success' => true, 'message' => 'Book updated successfully.', 'data' => $book->load('category')])
                : back()->with('success', 'Book updated.');
        } catch (UniqueConstraintViolationException) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'A book with this ISBN already exists in your catalog.'], 422)
                : back()->with('error', 'A book with this ISBN already exists in your catalog.');
        } catch (\Exception $e) {
            Log::error('Failed to update library book.', ['school_id' => $this->getSchoolId(), 'book_id' => $book->id, 'exception' => $e]);
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Failed to update book.'], 500)
                : back()->with('error', 'Failed to update book.');
        }
    }

    public function destroyBook(Book $book)
    {
        $this->ensureSchoolActive();
        $this->authorizeTenant($book);
        Gate::authorize('manage', Book::class);

        try {
            $book->delete();
            $this->bustStatsCache();

            return request()->wantsJson()
                ? response()->json(['success' => true, 'message' => 'Book removed from catalog.'])
                : back()->with('success', 'Book removed from catalog.');
        } catch (\Illuminate\Database\QueryException $e) {
            // FK restrict fires when active issues exist
            return request()->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Cannot delete a book that has circulation history.'], 422)
                : back()->with('error', 'Cannot delete a book that has circulation history.');
        }
    }

    // -------------------------------------------------------------------------
    // Categories
    // -------------------------------------------------------------------------

    public function storeCategory(Request $request)
    {
        $this->ensureSchoolActive();
        Gate::authorize('manage', Book::class);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255',
                Rule::unique('book_categories', 'name')->where('school_id', $this->getSchoolId())->whereNull('deleted_at')],
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $category = BookCategory::create([
                'school_id'   => $this->getSchoolId(),
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            return $request->wantsJson()
                ? response()->json(['success' => true, 'message' => 'Book category created successfully!', 'data' => $category])
                : back()->with('success', 'Book category created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create book category.', ['school_id' => $this->getSchoolId(), 'exception' => $e]);
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Failed to create book category. Please try again.'], 500)
                : back()->with('error', 'Failed to create book category. Please try again.');
        }
    }

    public function updateCategory(Request $request, BookCategory $category)
    {
        $this->ensureSchoolActive();
        $this->authorizeTenant($category);
        Gate::authorize('manage', Book::class);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255',
                Rule::unique('book_categories', 'name')->where('school_id', $this->getSchoolId())->whereNull('deleted_at')->ignore($category->id)],
            'description' => 'nullable|string|max:1000',
        ]);

        $category->update($validated);

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => 'Category updated.', 'data' => $category])
            : back()->with('success', 'Category updated.');
    }

    public function destroyCategory(BookCategory $category)
    {
        $this->ensureSchoolActive();
        $this->authorizeTenant($category);
        Gate::authorize('manage', Book::class);

        try {
            $category->delete();
            return request()->wantsJson()
                ? response()->json(['success' => true, 'message' => 'Category deleted.'])
                : back()->with('success', 'Category deleted.');
        } catch (\Illuminate\Database\QueryException $e) {
            return request()->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Cannot delete a category that has books assigned to it.'], 422)
                : back()->with('error', 'Cannot delete a category that has books assigned to it.');
        }
    }

    // -------------------------------------------------------------------------
    // Circulation
    // -------------------------------------------------------------------------

    public function issues(Request $request)
    {
        $this->ensureSchoolActive();
        Gate::authorize('manage', Book::class);

        $query = BookIssue::where('school_id', $this->getSchoolId())
            ->where('status', 'issued')
            ->with(['book', 'student']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(fn($q) => $q
                ->whereHas('student', fn($s) => $s
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('admission_no', 'like', "%{$search}%"))
                ->orWhereHas('book', fn($b) => $b
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")));
        }

        $sort      = $request->input('sort', 'due_date');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (in_array($sort, ['issue_date', 'due_date', 'id'], true)) {
            $query->orderBy($sort, $direction);
        }

        $transformer = function ($row) {
            $today     = now()->startOfDay();
            $dueDate   = $row->due_date->copy()->startOfDay();
            $isOverdue = $today->greaterThan($dueDate);
            return [
                'id'           => $row->id,
                'book_title'   => $row->book?->title ?? 'N/A',
                'student_name' => $row->student?->full_name ?? 'N/A',
                'admission_no' => $row->student?->admission_no ?? 'N/A',
                'issue_date'   => $row->issue_date->format('d M, Y'),
                'due_date'     => $row->due_date->format('d M, Y'),
                'overdue'      => $isOverdue,
                'overdue_days' => $isOverdue ? $dueDate->diffInDays($today) : 0,
            ];
        };

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $this->getTableStats());
        }

        $initialData = $this->getHydrationData($query, $transformer, ['stats' => $this->getTableStats()]);

        return view('school.library.issues', [
            'initialData'    => $initialData,
            'stats'          => $initialData['stats'],
            'currency'       => $this->getSchool()->settings['currency_symbol'] ?? '₹',
            'books'          => Book::where('school_id', $this->getSchoolId())->available()->get(),
            'studentSearch'  => route('school.library.students.search'),
        ]);
    }

    public function issueBook(Request $request)
    {
        $this->ensureSchoolActive();
        Gate::authorize('manage', Book::class);

        $request->validate([
            'book_id'    => ['required', Rule::exists('books', 'id')->where('school_id', $this->getSchoolId())],
            'student_id' => ['required', Rule::exists('students', 'id')->where('school_id', $this->getSchoolId())],
            'due_date'   => 'required|date|after:today',
        ]);

        try {
            $data              = $request->all();
            $data['school_id'] = $this->getSchoolId();
            $result            = $this->libraryService->issueBook($data);
            $this->bustStatsCache();

            return $request->wantsJson()
                ? response()->json([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'data'    => $result['success'] ? $result['issue']->load(['book', 'student']) : null,
                ], $result['success'] ? 200 : 422)
                : ($result['success'] ? back()->with('success', $result['message']) : back()->with('error', $result['message']));
        } catch (\Exception $e) {
            Log::error('Failed to issue library book.', ['school_id' => $this->getSchoolId(), 'exception' => $e]);
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Failed to issue book. Please try again.'], 500)
                : back()->with('error', 'Failed to issue book. Please try again.');
        }
    }

    public function returnBook(BookIssue $issue)
    {
        $this->ensureSchoolActive();
        $this->authorizeTenant($issue);
        Gate::authorize('manageIssue', $issue);

        try {
            $result = $this->libraryService->returnBook($issue);
            $this->bustStatsCache();

            return request()->wantsJson()
                ? response()->json([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'fine'    => $result['success'] ? $result['fine'] : 0,
                ], $result['success'] ? 200 : 422)
                : ($result['success'] ? back()->with('success', $result['message']) : back()->with('error', $result['message']));
        } catch (\Exception $e) {
            Log::error('Failed to return library book.', ['school_id' => $this->getSchoolId(), 'issue_id' => $issue->id, 'exception' => $e]);
            return request()->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Failed to process return. Please try again.'], 500)
                : back()->with('error', 'Failed to process return. Please try again.');
        }
    }

    public function markAsLost(BookIssue $issue)
    {
        $this->ensureSchoolActive();
        $this->authorizeTenant($issue);
        Gate::authorize('manageIssue', $issue);

        try {
            $result = $this->libraryService->markAsLost($issue);
            $this->bustStatsCache();

            return request()->wantsJson()
                ? response()->json(['success' => $result['success'], 'message' => $result['message']], $result['success'] ? 200 : 422)
                : ($result['success'] ? back()->with('success', $result['message']) : back()->with('error', $result['message']));
        } catch (\Exception $e) {
            Log::error('Failed to mark book as lost.', ['school_id' => $this->getSchoolId(), 'issue_id' => $issue->id, 'exception' => $e]);
            return request()->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Failed to process request.'], 500)
                : back()->with('error', 'Failed to process request.');
        }
    }

    public function settleFine(BookIssue $issue)
    {
        $this->ensureSchoolActive();
        $this->authorizeTenant($issue);
        Gate::authorize('manageIssue', $issue);

        try {
            $result = $this->libraryService->settleFine($issue);

            return request()->wantsJson()
                ? response()->json(['success' => $result['success'], 'message' => $result['message'], 'fine' => $result['fine'] ?? null], $result['success'] ? 200 : 422)
                : ($result['success'] ? back()->with('success', $result['message']) : back()->with('error', $result['message']));
        } catch (\Exception $e) {
            Log::error('Failed to settle library fine.', ['school_id' => $this->getSchoolId(), 'issue_id' => $issue->id, 'exception' => $e]);
            return request()->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Failed to settle fine.'], 500)
                : back()->with('error', 'Failed to settle fine.');
        }
    }

    // -------------------------------------------------------------------------
    // History
    // -------------------------------------------------------------------------

    public function history(Request $request)
    {
        $this->ensureSchoolActive();
        Gate::authorize('manage', Book::class);

        $query = BookIssue::where('school_id', $this->getSchoolId())
            ->whereIn('status', ['returned', 'lost'])
            ->with(['book', 'student']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(fn($q) => $q
                ->whereHas('student', fn($s) => $s
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('admission_no', 'like', "%{$search}%"))
                ->orWhereHas('book', fn($b) => $b
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")));
        }

        if ($request->filled('from_date')) {
            $query->where('updated_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->where('updated_at', '<=', $request->input('to_date') . ' 23:59:59');
        }

        $query->orderBy('updated_at', 'desc');

        $currency    = $this->getSchool()->settings['currency_symbol'] ?? '₹';
        $transformer = fn($row) => [
            'id'           => $row->id,
            'book_title'   => $row->book?->title ?? 'N/A',
            'student_name' => $row->student?->full_name ?? 'N/A',
            'admission_no' => $row->student?->admission_no ?? 'N/A',
            'issue_date'   => $row->issue_date->format('d M, Y'),
            'due_date'     => $row->due_date->format('d M, Y'),
            'return_date'  => $row->return_date?->format('d M, Y') ?? '—',
            'status'       => $row->status,
            'fine_amount'  => number_format((float) $row->fine_amount, 2),
            'fine_settled' => $row->isFineSettled(),
            'currency'     => $currency,
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, []);
        }

        $initialData = $this->getHydrationData($query, $transformer, []);

        return view('school.library.history', [
            'initialData' => $initialData,
            'currency'    => $currency,
        ]);
    }

    // -------------------------------------------------------------------------
    // Student AJAX search
    // -------------------------------------------------------------------------

    public function searchStudents(Request $request)
    {
        $this->ensureSchoolActive();
        Gate::authorize('manage', Book::class);

        $q       = $request->input('q', '');
        $results = Student::where('school_id', $this->getSchoolId())
            ->active()
            ->where(fn($query) => $query
                ->where('first_name', 'like', "%{$q}%")
                ->orWhere('last_name', 'like', "%{$q}%")
                ->orWhere('admission_no', 'like', "%{$q}%"))
            ->limit(20)
            ->get(['id', 'first_name', 'last_name', 'admission_no'])
            ->map(fn($s) => [
                'id'    => $s->id,
                'label' => $s->admission_no . ' — ' . $s->full_name,
            ]);

        return response()->json($results);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function getTableStats(): array
    {
        return Cache::remember("library_stats_{$this->getSchoolId()}", 60, fn() => [
            'total_books'      => Book::where('school_id', $this->getSchoolId())->sum('quantity'),
            'issued_books'     => BookIssue::where('school_id', $this->getSchoolId())->where('status', 'issued')->count(),
            'available_titles' => Book::where('school_id', $this->getSchoolId())->count(),
            'overdue_returns'  => BookIssue::where('school_id', $this->getSchoolId())
                ->where('status', 'issued')->where('due_date', '<', now())->count(),
        ]);
    }

    protected function bustStatsCache(): void
    {
        Cache::forget("library_stats_{$this->getSchoolId()}");
    }
}
