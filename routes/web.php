<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CaseController;
use App\Http\Controllers\Admin\ContentModuleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocalizationController;
use App\Http\Controllers\Admin\PeopleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

// Public Portal
Route::get('/', [PortalController::class, 'index'])->name('home');
Route::get('/track/{caseNo}', [PortalController::class, 'track'])->name('portal.track');

// Locale Switcher
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Web Authentication
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
Route::get('/dev/quick-login/{role}', [WebAuthController::class, 'quickLogin'])->name('dev.quick-login');

// Admin Panel Routes
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Applications / Cases Workspace
    Route::get('/cases', [CaseController::class, 'index'])->name('cases.index');
    Route::get('/cases/{id}', [CaseController::class, 'show'])->name('cases.show');
    Route::post('/cases/{id}/transition', [CaseController::class, 'transition'])->name('cases.transition');
    Route::post('/cases/{id}/note', [CaseController::class, 'addNote'])->name('cases.note');
    Route::post('/cases/{id}/assign', [CaseController::class, 'assign'])->name('cases.assign');
    Route::post('/cases/{id}/follow-up', [CaseController::class, 'addFollowUp'])->name('cases.follow-up');

    // People & Geographic Scoping
    Route::get('/people', [PeopleController::class, 'index'])->name('people.index');
    Route::post('/people', [PeopleController::class, 'store'])->name('people.store');
    Route::post('/people/{id}/toggle-active', [PeopleController::class, 'toggleActive'])->name('people.toggle-active');

    // Content & Community Modules
    Route::get('/content', [ContentModuleController::class, 'index'])->name('content.index');

    // Audit Trail
    Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');

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

    // Settings (General, SMTP, Notifications)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general');
    Route::post('/settings/smtp', [SettingsController::class, 'updateSmtp'])->name('settings.smtp');
    Route::post('/settings/test-email', [SettingsController::class, 'testEmail'])->name('settings.test-email');
    Route::post('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications');
    Route::post('/settings/notification-template/{id}/toggle', [SettingsController::class, 'toggleTemplate'])->name('settings.template.toggle');
});
