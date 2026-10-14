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
use App\Http\Controllers\CatalogController;
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
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if (Auth::user()->isManager()) {
            return redirect()->route('manager.dashboard');
        }
        return redirect()->route('sales.dashboard');
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

Route::get('/models', [CatalogController::class, 'index'])->name('models.catalog');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Sales Representative Web Routes (Protected by auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('sales')->name('sales.')->group(function () {
    Route::get('/dashboard', [SalesDashboardController::class, 'index'])->name('dashboard');
    Route::get('/models', [CatalogController::class, 'index'])->name('models');
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
    Route::delete('/enhancements/{enhancement}', [EnhancementController::class, 'destroy'])->name('enhancements.destroy');

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

    // Quotation Page & Pricing Basis Master Settings
    Route::get('/quotation-settings', [\App\Http\Controllers\Admin\QuotationSettingsController::class, 'index'])->name('quotation-settings.index');
    Route::put('/quotation-settings', [\App\Http\Controllers\Admin\QuotationSettingsController::class, 'update'])->name('quotation-settings.update');
    Route::post('/quotation-settings/reset', [\App\Http\Controllers\Admin\QuotationSettingsController::class, 'reset'])->name('quotation-settings.reset');
    Route::post('/quotation-settings/reset-model/{model}', [\App\Http\Controllers\Admin\QuotationSettingsController::class, 'resetModel'])->name('quotation-settings.reset-model');
    // Master Pricing shortcut in Admin
    Route::get('/pricing', fn () => redirect()->route('manager.pricing.index'))->name('pricing.index');
});

/*
|--------------------------------------------------------------------------
| Manager Routes - The Financial Authority & Commercial Master Pricing
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('manager')->name('manager.')->group(function () {
    // Commercial Master Pricing (Accessible by Manager and Admin)
    Route::middleware('role:manager,admin')->group(function () {
        Route::get('/pricing', [\App\Http\Controllers\Manager\PricingController::class, 'index'])->name('pricing.index');
        Route::put('/pricing/models/{model}', [\App\Http\Controllers\Manager\PricingController::class, 'updateModelPrice'])->name('pricing.models.update');
        Route::put('/pricing/enhancements/{enhancement}', [\App\Http\Controllers\Manager\PricingController::class, 'updateEnhancementPrice'])->name('pricing.enhancements.update');
        Route::put('/pricing/freight/{freight}', [\App\Http\Controllers\Manager\PricingController::class, 'updateFreightPrice'])->name('pricing.freight.update');
    });

    // Executive Dashboard, Sales Reports & Team Quotes (Manager ONLY - Financial Shielding)
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Manager\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/models', [CatalogController::class, 'index'])->name('models');
        Route::get('/reports', [\App\Http\Controllers\Manager\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [\App\Http\Controllers\Manager\ReportController::class, 'export'])->name('reports.export');
        Route::get('/quotes', [\App\Http\Controllers\Manager\QuoteController::class, 'index'])->name('quotes.index');
    });
});

