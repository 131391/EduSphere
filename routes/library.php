<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\School\LibraryController;

// Library Management — accessible to school_admin and librarian
Route::prefix('library')->name('library.')->group(function () {
    Route::get('/',                          [LibraryController::class, 'index'])->name('index');
    Route::post('/fetch',                    [LibraryController::class, 'index'])->name('fetch');

    // Books
    Route::post('/books',                    [LibraryController::class, 'storeBook'])->name('books.store');
    Route::put('/books/{book}',              [LibraryController::class, 'updateBook'])->name('books.update');
    Route::post('/books/{book}/adjust-stock',[LibraryController::class, 'adjustStock'])->name('books.adjust-stock');
    Route::delete('/books/{book}',           [LibraryController::class, 'destroyBook'])->name('books.destroy');

    // Categories
    Route::post('/categories',               [LibraryController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}',     [LibraryController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}',  [LibraryController::class, 'destroyCategory'])->name('categories.destroy');

    // Circulation
    Route::get('/issues',                    [LibraryController::class, 'issues'])->name('issues');
    Route::post('/issues/fetch',             [LibraryController::class, 'issues'])->name('issues.fetch');
    Route::post('/issue',                    [LibraryController::class, 'issueBook'])->name('issue.store');
    Route::post('/return/{issue}',           [LibraryController::class, 'returnBook'])->name('return');
    Route::post('/lost/{issue}',             [LibraryController::class, 'markAsLost'])->name('lost');
    Route::post('/recover/{issue}',          [LibraryController::class, 'recoverLost'])->name('recover');
    Route::post('/settle-fine/{issue}',      [LibraryController::class, 'settleFine'])->name('settle-fine');

    // History
    Route::get('/history',                   [LibraryController::class, 'history'])->name('history');
    Route::post('/history/fetch',            [LibraryController::class, 'history'])->name('history.fetch');

    // Student/Staff AJAX search — rate-limited to deter LIKE-based DoS scanning
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/students/search', [LibraryController::class, 'searchStudents'])->name('students.search');
        Route::get('/staff/search',    [LibraryController::class, 'searchStaff'])->name('staff.search');
    });

    Route::post('/renew/{issue}',            [LibraryController::class, 'renew'])->name('renew');

    // CSV exports — separate names so we can grant fine-grained access later.
    Route::get('/export/catalog',     [LibraryController::class, 'exportCatalog'])->name('export.catalog');
    Route::get('/export/circulation', [LibraryController::class, 'exportCirculation'])->name('export.circulation');
    Route::get('/export/history',     [LibraryController::class, 'exportHistory'])->name('export.history');
});
