<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\EnsureAdministrator;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MembershipApplicationController;
use App\Http\Controllers\WeatherController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Existing demonstration pages.
Route::get('/about', [PageController::class, 'about']);

Route::get('/tasks/{id}', [PageController::class, 'task']);

Route::get('/tasks', [TaskController::class, 'index'])
    ->name('tasks.index');

Route::post('/tasks', [TaskController::class, 'store'])
    ->name('tasks.store');

// Login is available to guests.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');
});

// Any signed-in user can sign out.
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/membership/apply', [
    MembershipApplicationController::class,
    'create',
])->name('membership.create');

Route::post('/membership/apply', [
    MembershipApplicationController::class,
    'store',
])
    ->middleware('throttle:5,1')
    ->name('membership.store');

Route::get('/weather', [WeatherController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('weather.show');

// Membership management requires an administrator account.
Route::middleware(['auth', EnsureAdministrator::class])
    ->group(function () {
        Route::get('/dashboard', [MemberController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/members', [MemberController::class, 'index'])
            ->name('members.index');

        Route::get('/members/create', [MemberController::class, 'create'])
            ->name('members.create');

        Route::post('/members', [MemberController::class, 'store'])
            ->name('members.store');

        Route::get('/members/{id}', [MemberController::class, 'show'])
            ->name('members.show');

        Route::get('/members/{id}/edit', [MemberController::class, 'edit'])
            ->name('members.edit');

        Route::put('/members/{id}', [MemberController::class, 'update'])
            ->name('members.update');

        Route::put(
            '/members/{id}/deactivate',
            [MemberController::class, 'deactivate']
        )->name('members.deactivate');

        Route::put(
            '/members/{member}/reactivate',
            [MemberController::class, 'reactivate']
        )->name('members.reactivate');

        Route::resource('categories', CategoryController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);

        Route::resource('levels', LevelController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);

        Route::resource('transactions', TransactionController::class)
            ->only(['index', 'create', 'store', 'show']);

        Route::put('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])
            ->name('transactions.cancel');

        Route::get('/payments', [PaymentController::class, 'index'])
            ->name('payments.index');

        Route::get(
            '/transactions/{transaction}/payments/create',
            [PaymentController::class, 'create']
        )->name('transactions.payments.create');

        Route::post(
            '/transactions/{transaction}/payments',
            [PaymentController::class, 'store']
        )->name('transactions.payments.store');

        Route::put(
            '/transactions/{transaction}/approve',
            [TransactionController::class, 'approve']
        )->name('transactions.approve');

        Route::get(
            '/payments/{payment}/receipt',
            [PaymentController::class, 'receipt']
        )->name('payments.receipt');

        Route::get(
            '/members/{member}/print',
            [MemberController::class, 'printRecord']
        )->name('members.print');

        Route::get('/applications', [
            MembershipApplicationController::class,
            'index',
        ])->name('applications.index');

        Route::get('/applications/{application}', [
            MembershipApplicationController::class,
            'show',
        ])->name('applications.show');

        Route::post(
            '/applications/{application}/process',
            [MembershipApplicationController::class, 'process']
        )->name('applications.process');
    });