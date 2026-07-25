<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BusinessSwitcherController;
use App\Http\Controllers\TransactionImportController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/privacy', function () { return view('privacy'); })->name('privacy');

Route::get('/dashboard', function () {
    return redirect()->route('books.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/unassigned', function () {
    return view('unassigned');
})->middleware(['auth'])->name('unassigned');

Route::middleware('auth')->group(function () {
    // Business selection and core resources
    Route::post('/business/switch/{business}', [BusinessSwitcherController::class, 'switch'])->name('business.switch');

    Route::middleware(['active.business'])->group(function () {
        Route::resource('businesses', BusinessController::class);
        Route::resource('books', \App\Http\Controllers\BookController::class);
        Route::get('books/{book}/transactions/data', [\App\Http\Controllers\BookController::class, 'transactionsData'])->name('books.transactions.data');
        Route::post('books/{book}/summary', [\App\Http\Controllers\BookController::class, 'summary'])->name('books.summary');

        // User search route
        Route::get('books/{book}/users/search', [\App\Http\Controllers\BookController::class, 'searchUsers'])->name('books.users.search');

        // Admin User Search & Cashbook Member Management (admin/primary_admin only)
        Route::middleware('business.role:primary_admin,admin')->group(function() {
            Route::get('/admin/users', [\App\Http\Controllers\AdminUserController::class, 'index'])->name('admin.users.index');
            Route::get('/admin/users/search', [\App\Http\Controllers\AdminUserController::class, 'search'])->name('admin.users.search');

            // Member management endpoints (supporting both /cashbooks and /books URL patterns)
            Route::post('/cashbooks/{book}/members', [\App\Http\Controllers\AdminUserController::class, 'addMember'])->name('cashbooks.members.add');
            Route::delete('/cashbooks/{book}/members/{user}', [\App\Http\Controllers\AdminUserController::class, 'removeMember'])->name('cashbooks.members.remove');
            Route::post('/books/{book}/members', [\App\Http\Controllers\AdminUserController::class, 'addMember'])->name('books.members.add');
            Route::delete('/books/{book}/members/{user}', [\App\Http\Controllers\AdminUserController::class, 'removeMember'])->name('books.members.remove');

            Route::get('books/{book}/users', [\App\Http\Controllers\BookController::class, 'users'])->name('books.users');
            Route::put('books/{book}/users/{user}/role', [\App\Http\Controllers\BookController::class, 'updateUserRole'])->name('books.users.role');
            Route::delete('books/{book}/users/{user}', [\App\Http\Controllers\BookController::class, 'removeUser'])->name('books.users.remove');
            Route::post('books/{book}/transfer-ownership', [\App\Http\Controllers\BookController::class, 'transferOwnership'])->name('books.transfer-ownership');
        });

        Route::resource('categories', \App\Http\Controllers\CategoryController::class)->except(['show']);
        Route::get('transactions/contacts', [\App\Http\Controllers\TransactionController::class, 'contacts'])->name('transactions.contacts');
        Route::get('transactions/create', [\App\Http\Controllers\TransactionController::class, 'create'])->name('transactions.create');
        Route::post('transactions', [\App\Http\Controllers\TransactionController::class, 'store'])->name('transactions.store');
        Route::get('transactions/{transaction}/edit', [\App\Http\Controllers\TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('transactions/{transaction}', [\App\Http\Controllers\TransactionController::class, 'update'])->name('transactions.update');
        Route::delete('transactions/{transaction}', [\App\Http\Controllers\TransactionController::class, 'destroy'])->name('transactions.destroy');
        Route::post('/transactions/bulk-delete', [TransactionController::class, 'bulkDelete'])->name('transactions.bulk-delete');
        Route::get('transactions/{transaction}/detail', [\App\Http\Controllers\TransactionController::class, 'detail'])->name('transactions.detail');
        Route::post('transactions/{transaction}/approve', [\App\Http\Controllers\TransactionController::class, 'approve'])->name('transactions.approve');
        Route::post('transactions/{transaction}/reject', [\App\Http\Controllers\TransactionController::class, 'reject'])->name('transactions.reject');
        Route::get('transactions/{transaction}/receipt', [\App\Http\Controllers\TransactionController::class, 'receipt'])->name('transactions.receipt');

        // Transaction Import Routes
        Route::get('/books/{book}/transactions/import', [TransactionImportController::class, 'create'])->name('transactions.import.create');
        Route::post('/books/{book}/transactions/import', [TransactionImportController::class, 'store'])->name('transactions.import.store');
        Route::get('/books/{book}/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/books/{book}/reports', [ReportController::class, 'generate'])->name('reports.generate');
        Route::get('/books/{book}/reports/download', [ReportController::class, 'download'])->name('reports.download');
        Route::delete('settings/leave', [\App\Http\Controllers\TeamController::class, 'leave'])->name('settings.leave');

        Route::middleware('business.role:primary_admin,admin')->group(function(){
            Route::get('settings', [\App\Http\Controllers\TeamController::class, 'index'])->name('settings.index');
            Route::post('settings/business', [\App\Http\Controllers\TeamController::class, 'updateBusiness'])->name('settings.business.update');
            Route::post('settings/invite', [\App\Http\Controllers\TeamController::class, 'invite'])->name('settings.invite');
            Route::post('settings/member/{user}/role', [\App\Http\Controllers\TeamController::class, 'updateRole'])->name('settings.member.role');
            Route::delete('settings/member/{user}', [\App\Http\Controllers\TeamController::class, 'remove'])->name('settings.member.remove');
        });

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
