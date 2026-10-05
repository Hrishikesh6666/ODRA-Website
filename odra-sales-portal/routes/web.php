<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EnhancementController;
use App\Http\Controllers\Admin\FreightController;
use App\Http\Controllers\Admin\ModelController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\CatalogApiController;
use App\Http\Controllers\Api\QuoteApiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MicrosoftAuthController;
use App\Http\Controllers\Sales\DashboardController as SalesDashboardController;
use App\Http\Controllers\Sales\PdfController;
use App\Http\Controllers\Sales\QuoteController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / Guest Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('sales.dashboard');
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Microsoft 365 SSO Routes
    Route::get('/auth/microsoft', [MicrosoftAuthController::class, 'redirectToMicrosoft'])->name('auth.microsoft');
    Route::get('/auth/microsoft/callback', [MicrosoftAuthController::class, 'handleMicrosoftCallback'])->name('auth.microsoft.callback');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Sales Representative Web Routes (Protected by auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('sales')->name('sales.')->group(function () {
    Route::get('/dashboard', [SalesDashboardController::class, 'index'])->name('dashboard');
    Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::get('/quotes/{quote}/pdf', [PdfController::class, 'download'])->name('quotes.pdf');
});

/*
|--------------------------------------------------------------------------
| Authenticated API Routes for Quote Builder & Catalog Data
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::get('/catalog', [CatalogApiController::class, 'index'])->name('catalog.index');

    Route::get('/quotes', [QuoteApiController::class, 'index'])->name('quotes.index');
    Route::post('/quotes', [QuoteApiController::class, 'store'])->name('quotes.store');
    Route::get('/quotes/{quote}', [QuoteApiController::class, 'show'])->name('quotes.show');
    Route::put('/quotes/{quote}', [QuoteApiController::class, 'update'])->name('quotes.update');
    Route::post('/quotes/{quote}/finalize', [QuoteApiController::class, 'finalize'])->name('quotes.finalize');
    Route::post('/quotes/{quote}/revise', [QuoteApiController::class, 'revise'])->name('quotes.revise');
    Route::post('/quotes/{quote}/pdf', [QuoteApiController::class, 'uploadPdf'])->name('quotes.pdf');
    Route::delete('/quotes/{quote}', [QuoteApiController::class, 'destroy'])->name('quotes.destroy');
});

/*
|--------------------------------------------------------------------------
| Administrator Routes (Protected by auth & role:admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Models & Master Pricing
    Route::get('/models', [ModelController::class, 'index'])->name('models.index');
    Route::get('/models/create', [ModelController::class, 'create'])->name('models.create');
    Route::post('/models', [ModelController::class, 'store'])->name('models.store');
    Route::get('/models/{model}/edit', [ModelController::class, 'edit'])->name('models.edit');
    Route::put('/models/{model}', [ModelController::class, 'update'])->name('models.update');
    Route::post('/models/{model}/toggle', [ModelController::class, 'toggle'])->name('models.toggle');

    // Enhancements & Parts
    Route::get('/enhancements', [EnhancementController::class, 'index'])->name('enhancements.index');
    Route::post('/enhancements', [EnhancementController::class, 'store'])->name('enhancements.store');
    Route::put('/enhancements/{enhancement}', [EnhancementController::class, 'update'])->name('enhancements.update');
    Route::post('/enhancements/{enhancement}/toggle', [EnhancementController::class, 'toggle'])->name('enhancements.toggle');

    // Freight Matrix
    Route::get('/freight', [FreightController::class, 'index'])->name('freight.index');
    Route::post('/freight', [FreightController::class, 'store'])->name('freight.store');
    Route::put('/freight/{freight}', [FreightController::class, 'update'])->name('freight.update');

    // Sales Representatives & Administrators
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Security Audit Log
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
});
