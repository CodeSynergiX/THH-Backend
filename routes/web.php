<?php

use App\Http\Controllers\Admin\LocalizationController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

// Public Portal
Route::get('/', [PortalController::class, 'index'])->name('home');
Route::get('/track/{caseNo}', [PortalController::class, 'track'])->name('portal.track');

// Locale Switcher
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Admin Panel Routes (Step 3: Theme & Localization)
Route::prefix('admin')->name('admin.')->group(function () {
    // Theme Management
    Route::get('/theme', [ThemeController::class, 'index'])->name('theme.index');
    Route::post('/theme/publish', [ThemeController::class, 'publish'])->name('theme.publish');
    Route::post('/theme/rollback/{id}', [ThemeController::class, 'rollback'])->name('theme.rollback');

    // Localization Management
    Route::get('/localization', [LocalizationController::class, 'index'])->name('localization.index');
    Route::post('/localization', [LocalizationController::class, 'update'])->name('localization.update');
    Route::get('/localization/scan-missing', [LocalizationController::class, 'scanMissing'])->name('localization.scan-missing');
    Route::get('/localization/export/json/{locale}', [LocalizationController::class, 'exportJson'])->name('localization.export.json');
    Route::get('/localization/export/csv', [LocalizationController::class, 'exportCsv'])->name('localization.export.csv');
});
