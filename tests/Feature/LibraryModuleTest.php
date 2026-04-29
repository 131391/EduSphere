<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookIssue;
use App\Models\ClassModel;
use App\Models\Role;
use App\Models\School;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryModuleTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->school = $this->createSchool();
        $this->adminUser = $this->createUser([
            'school_id' => $this->school->id,
            'role_id'   => Role::where('slug', Role::SCHOOL_ADMIN)->first()->id,
        ]);

        $this->setCurrentSchool($this->school);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_library_pages_render_and_fetch_endpoints_work(): void
    {
        $category = $this->createBookCategory();
        $book     = $this->createBook($category, ['title' => 'Atomic Habits']);
        $student  = $this->createStudent();

        BookIssue::create([
            'school_id'  => $this->school->id,
            'book_id'    => $book->id,
            'student_id' => $student->id,
            'issue_date' => '2026-01-10',
            'due_date'   => '2026-01-20',
            'status'     => 'issued',
        ]);

        $this->actingAsUser($this->adminUser)
            ->get($this->tenantUrl('/school/library'))
            ->assertOk();

        $this->actingAsUser($this->adminUser)
            ->get($this->tenantUrl('/school/library/issues'))
            ->assertOk();

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/fetch'), ['search' => 'Atomic'])
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Atomic Habits');

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/issues/fetch'), ['search' => 'Atomic'])
            ->assertOk()
            ->assertJsonPath('data.0.book_title', 'Atomic Habits');
    }

    public function test_can_create_book_category_from_library_module(): void
    {
        $response = $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/categories'), [
                'name'        => 'Reference',
                'description' => 'High-use library reference material.',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Reference');

        $this->assertDatabaseHas('book_categories', [
            'school_id' => $this->school->id,
            'name'      => 'Reference',
        ]);
    }

    public function test_second_issue_fails_when_book_is_out_of_stock(): void
    {
        Carbon::setTestNow('2026-01-10 09:00:00');

        $category = $this->createBookCategory();
        $book     = $this->createBook($category, ['quantity' => 1, 'available_quantity' => 1]);
        $student  = $this->createStudent();

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/issue'), [
                'book_id'    => $book->id,
                'student_id' => $student->id,
                'due_date'   => '2026-01-11',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/issue'), [
                'book_id'    => $book->id,
                'student_id' => $student->id,
                'due_date'   => '2026-01-12',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Book not available in stock.');

        $this->assertSame(0, $book->fresh()->available_quantity);
        $this->assertSame(1, BookIssue::where('book_id', $book->id)->count());
    }

    public function test_return_uses_configured_fine_and_prevents_double_return(): void
    {
        $this->school->update(['settings' => ['late_return_library_book_fine' => 7]]);

        Carbon::setTestNow('2026-01-10 09:00:00');

        $category = $this->createBookCategory();
        $book     = $this->createBook($category, ['quantity' => 1, 'available_quantity' => 1]);
        $student  = $this->createStudent();

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/issue'), [
                'book_id'    => $book->id,
                'student_id' => $student->id,
                'due_date'   => '2026-01-11',
            ])
            ->assertOk()->assertJsonPath('success', true);

        $issue = BookIssue::firstOrFail();
        $this->assertSame(0, $book->fresh()->available_quantity);

        Carbon::setTestNow('2026-01-15 09:00:00');

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/return/{$issue->id}"))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('fine', '28.00');

        $this->assertSame('returned', $issue->fresh()->status);
        $this->assertSame(1, $book->fresh()->available_quantity);

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/return/{$issue->id}"))
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'This book is already marked as returned');

        $this->assertSame(1, $book->fresh()->available_quantity);
    }

    public function test_issues_search_excludes_returned_records(): void
    {
        $category = $this->createBookCategory();
        $student  = $this->createStudent();

        $returnedBook = $this->createBook($category, ['title' => 'Chemistry Manual']);
        BookIssue::create([
            'school_id'   => $this->school->id,
            'book_id'     => $returnedBook->id,
            'student_id'  => $student->id,
            'issue_date'  => '2026-01-01',
            'due_date'    => '2026-01-05',
            'return_date' => '2026-01-04',
            'status'      => 'returned',
        ]);

        $activeBook = $this->createBook($category, ['title' => 'Physics Manual']);
        BookIssue::create([
            'school_id'  => $this->school->id,
            'book_id'    => $activeBook->id,
            'student_id' => $student->id,
            'issue_date' => '2026-01-10',
            'due_date'   => '2026-01-20',
            'status'     => 'issued',
        ]);

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/issues/fetch'), ['search' => 'Chemistry'])
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_mark_as_lost_decrements_quantity_and_applies_fine(): void
    {
        Carbon::setTestNow('2026-01-10 09:00:00');

        $category = $this->createBookCategory();
        $book     = $this->createBook($category, ['quantity' => 2, 'available_quantity' => 1, 'price' => 350.00]);
        $student  = $this->createStudent();

        $issue = BookIssue::create([
            'school_id'  => $this->school->id,
            'book_id'    => $book->id,
            'student_id' => $student->id,
            'issue_date' => '2026-01-10',
            'due_date'   => '2026-01-20',
            'status'     => 'issued',
        ]);

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/lost/{$issue->id}"))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('lost', $issue->fresh()->status);
        $this->assertSame(1, $book->fresh()->quantity);
        $this->assertSame('350.00', number_format((float) $issue->fresh()->fine_amount, 2));
    }

    public function test_mark_as_lost_is_idempotent(): void
    {
        $category = $this->createBookCategory();
        $book     = $this->createBook($category, ['quantity' => 1, 'available_quantity' => 0]);
        $student  = $this->createStudent();

        $issue = BookIssue::create([
            'school_id'  => $this->school->id,
            'book_id'    => $book->id,
            'student_id' => $student->id,
            'issue_date' => '2026-01-10',
            'due_date'   => '2026-01-20',
            'status'     => 'lost',
        ]);

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/lost/{$issue->id}"))
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_settle_fine_marks_fine_as_paid(): void
    {
        $category = $this->createBookCategory();
        $book     = $this->createBook($category);
        $student  = $this->createStudent();

        $issue = BookIssue::create([
            'school_id'   => $this->school->id,
            'book_id'     => $book->id,
            'student_id'  => $student->id,
            'issue_date'  => '2026-01-01',
            'due_date'    => '2026-01-05',
            'return_date' => '2026-01-10',
            'fine_amount' => 25.00,
            'status'      => 'returned',
        ]);

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/settle-fine/{$issue->id}"))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotNull($issue->fresh()->fine_paid_at);
    }

    public function test_settle_fine_prevents_double_settlement(): void
    {
        $category = $this->createBookCategory();
        $student  = $this->createStudent();

        $issue = BookIssue::create([
            'school_id'    => $this->school->id,
            'book_id'      => $this->createBook($category)->id,
            'student_id'   => $student->id,
            'issue_date'   => '2026-01-01',
            'due_date'     => '2026-01-05',
            'return_date'  => '2026-01-10',
            'fine_amount'  => 25.00,
            'fine_paid_at' => now(),
            'status'       => 'returned',
        ]);

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/settle-fine/{$issue->id}"))
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Fine has already been settled.');
    }

    public function test_history_endpoint_returns_returned_and_lost_records_only(): void
    {
        $category = $this->createBookCategory();
        $student  = $this->createStudent();

        $returnedBook = $this->createBook($category, ['title' => 'History Book']);
        BookIssue::create([
            'school_id'   => $this->school->id,
            'book_id'     => $returnedBook->id,
            'student_id'  => $student->id,
            'issue_date'  => '2026-01-01',
            'due_date'    => '2026-01-05',
            'return_date' => '2026-01-04',
            'status'      => 'returned',
        ]);

        $activeBook = $this->createBook($category, ['title' => 'Active Book']);
        BookIssue::create([
            'school_id'  => $this->school->id,
            'book_id'    => $activeBook->id,
            'student_id' => $student->id,
            'issue_date' => '2026-01-10',
            'due_date'   => '2026-01-20',
            'status'     => 'issued',
        ]);

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/history/fetch'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.book_title', 'History Book');
    }

    public function test_librarian_can_access_library_routes(): void
    {
        $librarianRole = Role::where('slug', Role::LIBRARIAN)->first();
        $librarian     = $this->createUser([
            'school_id' => $this->school->id,
            'role_id'   => $librarianRole->id,
        ]);

        $this->actingAsUser($librarian)
            ->get($this->tenantUrl('/school/library'))
            ->assertOk();
    }

    public function test_student_role_cannot_access_library_management(): void
    {
        $studentRole = Role::where('slug', Role::STUDENT)->first();
        $studentUser = $this->createUser([
            'school_id' => $this->school->id,
            'role_id'   => $studentRole->id,
        ]);

        $this->actingAsUser($studentUser)
            ->get($this->tenantUrl('/school/library'))
            ->assertForbidden();
    }

    public function test_books_from_school_a_invisible_to_school_b(): void
    {
        // Book belongs to default $this->school. Spin up a sibling school + admin.
        $bookA = $this->createBook($this->createBookCategory(), ['title' => 'Tenant-A Title']);

        $schoolB = $this->createSchool();
        $adminB  = $this->createUser([
            'school_id' => $schoolB->id,
            'role_id'   => Role::where('slug', Role::SCHOOL_ADMIN)->first()->id,
        ]);

        $payload = ['title' => 'Updated', 'author' => 'X', 'category_id' => 0];

        // Index list of school B must not include school A's book.
        $this->actingAsUser($adminB)
            ->postJson('http://' . $schoolB->subdomain . '.localhost/school/library/fetch')
            ->assertOk()
            ->assertJsonMissing(['title' => 'Tenant-A Title']);

        // Direct route-model binding to school A's book id from school B context — 404.
        $this->actingAsUser($adminB)
            ->putJson('http://' . $schoolB->subdomain . '.localhost/school/library/books/' . $bookA->id, $payload)
            ->assertNotFound();
    }

    public function test_duplicate_active_issue_for_same_student_rejected(): void
    {
        Carbon::setTestNow('2026-01-10 09:00:00');

        $category = $this->createBookCategory();
        $book     = $this->createBook($category, ['quantity' => 5, 'available_quantity' => 5]);
        $student  = $this->createStudent();

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/issue'), [
                'book_id'    => $book->id,
                'student_id' => $student->id,
                'due_date'   => '2026-01-20',
            ])->assertOk();

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/issue'), [
                'book_id'    => $book->id,
                'student_id' => $student->id,
                'due_date'   => '2026-01-22',
            ])->assertStatus(422)
              ->assertJsonPath('message', 'This student already has an active issue for this book.');

        $this->assertSame(1, BookIssue::where('book_id', $book->id)->where('student_id', $student->id)->count());
    }

    public function test_renew_rejected_when_already_overdue(): void
    {
        Carbon::setTestNow('2026-01-10 09:00:00');

        $category = $this->createBookCategory();
        $book     = $this->createBook($category, ['quantity' => 1, 'available_quantity' => 1]);
        $student  = $this->createStudent();

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/issue'), [
                'book_id'    => $book->id,
                'student_id' => $student->id,
                'due_date'   => '2026-01-15',
            ])->assertOk();

        $issue = BookIssue::firstOrFail();

        // Move time past the due date — renewal must now be refused.
        Carbon::setTestNow('2026-01-20 09:00:00');

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/renew/{$issue->id}"), [
                'due_date' => '2026-01-30',
            ])->assertStatus(422)
              ->assertJsonPath('success', false);
    }

    public function test_renew_extends_due_date_and_increments_renewal_count(): void
    {
        Carbon::setTestNow('2026-01-10 09:00:00');

        $category = $this->createBookCategory();
        $book     = $this->createBook($category, ['quantity' => 1, 'available_quantity' => 1]);
        $student  = $this->createStudent();

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/issue'), [
                'book_id'    => $book->id,
                'student_id' => $student->id,
                'due_date'   => '2026-01-15',
            ])->assertOk();

        $issue = BookIssue::firstOrFail();

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/renew/{$issue->id}"), [
                'due_date' => '2026-01-25',
            ])->assertOk()
              ->assertJsonPath('success', true);

        $issue->refresh();
        $this->assertSame('2026-01-25', $issue->due_date->toDateString());
        $this->assertSame(1, (int) $issue->renewal_count);
    }

    public function test_recover_lost_book_restores_inventory_and_voids_unpaid_fine(): void
    {
        $category = $this->createBookCategory();
        $book     = $this->createBook($category, ['quantity' => 2, 'available_quantity' => 1, 'price' => 400]);
        $student  = $this->createStudent();

        $issue = BookIssue::create([
            'school_id'   => $this->school->id,
            'book_id'     => $book->id,
            'student_id'  => $student->id,
            'issue_date'  => '2026-01-10',
            'due_date'    => '2026-01-20',
            'status'      => 'lost',
            'fine_amount' => 400,
        ]);
        // Simulate lost-state inventory adjustment that markAsLost() would do.
        $book->update(['quantity' => 1]);

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/recover/{$issue->id}"))
            ->assertOk()
            ->assertJsonPath('success', true);

        $issue->refresh();
        $book->refresh();

        $this->assertSame('returned', $issue->status);
        $this->assertSame('0.00', (string) $issue->fine_amount);
        $this->assertSame(2, $book->quantity);
        $this->assertSame(2, $book->available_quantity);
    }

    public function test_adjust_stock_increases_total_and_available(): void
    {
        $category = $this->createBookCategory();
        $book     = $this->createBook($category, ['quantity' => 3, 'available_quantity' => 2]);

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/books/{$book->id}/adjust-stock"), [
                'delta' => 5, 'reason' => 'purchase',
            ])->assertOk()
              ->assertJsonPath('success', true)
              ->assertJsonPath('quantity', 8)
              ->assertJsonPath('available_quantity', 7);
    }

    public function test_adjust_stock_cannot_drop_below_currently_issued(): void
    {
        $category = $this->createBookCategory();
        // 3 total, 1 available => 2 currently out
        $book     = $this->createBook($category, ['quantity' => 3, 'available_quantity' => 1]);

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/books/{$book->id}/adjust-stock"), [
                'delta' => -2, 'reason' => 'shrinkage',
            ])->assertStatus(422)
              ->assertJsonPath('success', false);
    }

    public function test_borrower_cap_blocks_extra_issues(): void
    {
        $this->school->update(['settings' => ['library_max_books_per_borrower' => 2]]);

        $category = $this->createBookCategory();
        $b1 = $this->createBook($category, ['title' => 'B1']);
        $b2 = $this->createBook($category, ['title' => 'B2']);
        $b3 = $this->createBook($category, ['title' => 'B3']);
        $student = $this->createStudent();

        foreach ([$b1, $b2] as $b) {
            $this->actingAsUser($this->adminUser)
                ->postJson($this->tenantUrl('/school/library/issue'), [
                    'book_id'    => $b->id,
                    'student_id' => $student->id,
                    'due_date'   => '2030-01-01',
                ])->assertOk()->assertJsonPath('success', true);
        }

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/issue'), [
                'book_id'    => $b3->id,
                'student_id' => $student->id,
                'due_date'   => '2030-01-01',
            ])->assertStatus(422)
              ->assertJsonPath('success', false);
    }

    public function test_back_dated_return_lowers_calculated_fine(): void
    {
        $this->school->update(['settings' => ['late_return_library_book_fine' => 10]]);

        Carbon::setTestNow('2026-01-10 09:00:00');

        $category = $this->createBookCategory();
        $book     = $this->createBook($category, ['quantity' => 1, 'available_quantity' => 1]);
        $student  = $this->createStudent();

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl('/school/library/issue'), [
                'book_id'    => $book->id,
                'student_id' => $student->id,
                'due_date'   => '2026-01-15',
            ])->assertOk();

        $issue = BookIssue::firstOrFail();

        // It is now 25 Jan; if back-dated to 18 Jan, only 3 days overdue × 10 = 30.
        Carbon::setTestNow('2026-01-25 09:00:00');

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/return/{$issue->id}"), [
                'return_date' => '2026-01-18',
            ])->assertOk()
              ->assertJsonPath('fine', '30.00');
    }

    public function test_fine_settlement_records_amount_method_and_collector(): void
    {
        $category = $this->createBookCategory();
        $book     = $this->createBook($category);
        $student  = $this->createStudent();

        $issue = BookIssue::create([
            'school_id'   => $this->school->id,
            'book_id'     => $book->id,
            'student_id'  => $student->id,
            'issue_date'  => '2026-01-01',
            'due_date'    => '2026-01-05',
            'return_date' => '2026-01-10',
            'fine_amount' => 50.00,
            'status'      => 'returned',
        ]);

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/settle-fine/{$issue->id}"), [
                'paid_amount'    => 50,
                'payment_method' => 'upi',
                'note'           => 'Receipt #42',
            ])->assertOk()
              ->assertJsonPath('success', true);

        $issue->refresh();
        $this->assertNotNull($issue->fine_paid_at);
        $this->assertSame('50.00', (string) $issue->fine_paid_amount);
        $this->assertSame('upi', $issue->fine_payment_method);
        $this->assertSame($this->adminUser->id, (int) $issue->fine_collected_by);
        $this->assertSame('Receipt #42', $issue->fine_settlement_note);
    }

    public function test_partial_settlement_amount_capped_at_outstanding(): void
    {
        $category = $this->createBookCategory();
        $book     = $this->createBook($category);
        $student  = $this->createStudent();

        $issue = BookIssue::create([
            'school_id'   => $this->school->id,
            'book_id'     => $book->id,
            'student_id'  => $student->id,
            'issue_date'  => '2026-01-01',
            'due_date'    => '2026-01-05',
            'return_date' => '2026-01-10',
            'fine_amount' => 25.00,
            'status'      => 'returned',
        ]);

        $this->actingAsUser($this->adminUser)
            ->postJson($this->tenantUrl("/school/library/settle-fine/{$issue->id}"), [
                'paid_amount'    => 100,
                'payment_method' => 'cash',
            ])->assertStatus(422)
              ->assertJsonPath('success', false);
    }

    public function test_csv_export_catalog_returns_csv(): void
    {
        $category = $this->createBookCategory();
        $this->createBook($category, ['title' => 'Exported Book']);

        $response = $this->actingAsUser($this->adminUser)
            ->get($this->tenantUrl('/school/library/export/catalog'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Exported Book', $response->streamedContent());
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function createBookCategory(array $attributes = []): BookCategory
    {
        static $counter = 0;
        $counter++;

        return BookCategory::create(array_merge([
            'school_id'   => $this->school->id,
            'name'        => "Category {$counter}",
            'description' => 'Default library category',
        ], $attributes));
    }

    protected function createBook(BookCategory $category, array $attributes = []): Book
    {
        static $counter = 0;
        $counter++;

        return Book::create(array_merge([
            'school_id'          => $this->school->id,
            'category_id'        => $category->id,
            'title'              => "Sample Book {$counter}",
            'author'             => 'Sample Author',
            'isbn'               => "978000000{$counter}",
            'quantity'           => 3,
            'available_quantity' => 3,
            'price'              => 499.00,
        ], $attributes));
    }

    protected function createStudent(array $attributes = []): Student
    {
        $academicYear = AcademicYear::factory()->create(['school_id' => $this->school->id]);
        $class        = ClassModel::factory()->create(['school_id' => $this->school->id]);
        $section      = Section::factory()->create(['school_id' => $this->school->id, 'class_id' => $class->id]);
        $studentUser  = User::factory()->create(['school_id' => $this->school->id]);

        return Student::factory()->active()->create(array_merge([
            'school_id'        => $this->school->id,
            'user_id'          => $studentUser->id,
            'academic_year_id' => $academicYear->id,
            'class_id'         => $class->id,
            'section_id'       => $section->id,
        ], $attributes));
    }

    protected function tenantUrl(string $path): string
    {
        return 'http://' . $this->school->subdomain . '.localhost' . $path;
    }
}
