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
    Route::post('/settle-fine/{issue}',      [LibraryController::class, 'settleFine'])->name('settle-fine');

    // History
    Route::get('/history',                   [LibraryController::class, 'history'])->name('history');
    Route::post('/history/fetch',            [LibraryController::class, 'history'])->name('history.fetch');

    // Student AJAX search (replaces full <select> load)
    Route::get('/students/search',           [LibraryController::class, 'searchStudents'])->name('students.search');
});
