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

// Web-based Database Setup for environments without interactive Shell (e.g. Render Free tier)
Route::get('/setup-db', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = \Illuminate\Support\Facades\Artisan::output();

        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        $seedOutput = \Illuminate\Support\Facades\Artisan::output();

        return response("<!DOCTYPE html><html><head><title>Database Setup</title><meta name='viewport' content='width=device-width, initial-scale=1'></head><body style='font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;padding:30px;background:#f1f5f9;color:#0f172a;'><div style='max-width:700px;margin:20px auto;background:#fff;border-radius:12px;padding:32px;box-shadow:0 4px 12px rgba(0,0,0,0.08);'><div style='display:flex;align-items:center;gap:12px;margin-bottom:16px;'><div style='width:40px;height:40px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;color:#16a34a;font-size:22px;font-weight:bold;'>&#10003;</div><h2 style='margin:0;font-size:24px;color:#0f172a;'>Database Setup Succeeded!</h2></div><p style='color:#475569;margin-bottom:20px;'>All tables and default records (Administrator, Sales Reps, Models, Chassis, Pricing, Enhancements, Freight) are successfully initialized.</p><h4 style='margin:16px 0 8px 0;color:#334155;'>Artisan Migration & Seed Output:</h4><pre style='background:#0f172a;color:#38bdf8;padding:16px;border-radius:8px;font-size:13px;line-height:1.5;overflow-x:auto;max-height:350px;'><b>[MIGRATIONS]</b>\n" . htmlspecialchars($migrateOutput) . "\n\n<b>[SEEDERS]</b>\n" . htmlspecialchars($seedOutput) . "</pre><div style='margin-top:24px;display:flex;gap:12px;'><a href='/login' style='background:#b91c1c;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;display:inline-block;'>Go to Portal Login &rarr;</a><a href='/' style='background:#e2e8f0;color:#1e293b;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;display:inline-block;'>Home</a></div></div></body></html>");
    } catch (\Throwable $e) {
        return response("<!DOCTYPE html><html><head><title>Database Setup Error</title><meta name='viewport' content='width=device-width, initial-scale=1'></head><body style='font-family:sans-serif;padding:30px;background:#fef2f2;'><div style='max-width:700px;margin:20px auto;background:#fff;border-radius:12px;padding:32px;box-shadow:0 4px 12px rgba(0,0,0,0.08);'><h2 style='color:#dc2626;margin-top:0;'>Database Connection / Setup Failed</h2><p style='color:#7f1d1d;'>Please verify your database credentials on Render.</p><pre style='background:#fee2e2;color:#991b1b;padding:16px;border-radius:8px;font-size:13px;white-space:pre-wrap;'>" . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . "</pre><div style='margin-top:20px;'><a href='/setup-db' style='background:#dc2626;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;'>Retry Setup</a></div></div></body></html>", 500);
    }
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
]);

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

