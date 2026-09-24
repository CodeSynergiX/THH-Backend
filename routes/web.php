<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CaseController;
use App\Http\Controllers\Admin\ContentModuleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocalizationController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\NotificationInboxController;
use App\Http\Controllers\Admin\PeopleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StaticPageController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

// Public Portal
Route::middleware('public_portal')->group(function () {
    Route::get('/', [PortalController::class, 'index'])->name('home');
    Route::get('/track/{caseNo}', [PortalController::class, 'track'])->name('portal.track');
    Route::post('/track/otp', [PortalController::class, 'requestTrackOtp'])->name('portal.track.otp');
    Route::get('/community/{module}', [PortalController::class, 'showModule'])->name('portal.community.show');
    Route::get('/community/{module}/{item}', [PortalController::class, 'showItem'])->name('portal.community.item');
    Route::post('/community/{module}/apply', [PortalController::class, 'applyHelp'])->name('portal.community.apply');
    Route::get('/about', fn () => app(PortalController::class)->showStaticPage('about-us'))->name('portal.about');
    Route::get('/privacy', fn () => app(PortalController::class)->showStaticPage('privacy-policy'))->name('portal.privacy');
    Route::get('/terms', fn () => app(PortalController::class)->showStaticPage('terms-conditions'))->name('portal.terms');
});

// Locale Switcher
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Web Authentication
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/login/otp/request', [WebAuthController::class, 'requestLoginOtp'])->name('login.otp.request');
Route::post('/login/otp/verify', [WebAuthController::class, 'verifyLoginOtp'])->name('login.otp.verify');
Route::get('/forgot-password', [WebAuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [WebAuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [WebAuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])->name('password.update');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
Route::get('/dev/quick-login/{role}', [WebAuthController::class, 'quickLogin'])->name('dev.quick-login');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/applications', [AccountController::class, 'applications'])->name('applications');
    Route::get('/applications/{id}', [AccountController::class, 'show'])->name('applications.show');
    Route::post('/applications/{id}/confirm', [AccountController::class, 'confirm'])->name('applications.confirm');
    Route::post('/applications/{id}/reopen', [AccountController::class, 'reopen'])->name('applications.reopen');
    Route::get('/appointments', [AccountController::class, 'appointments'])->name('appointments');
    Route::get('/notifications', [AccountController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/{id}/read', [AccountController::class, 'markNotificationRead'])->name('notifications.read');
});

// Admin Panel Routes
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::middleware('permission:cases.view')->group(function () {
        Route::get('/cases', [CaseController::class, 'index'])->name('cases.index');
        Route::get('/cases/{id}', [CaseController::class, 'show'])->name('cases.show');
        Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/notifications', [NotificationInboxController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [NotificationInboxController::class, 'markRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationInboxController::class, 'markAllRead'])->name('notifications.read-all');
    });

    Route::middleware('permission:cases.manage')->group(function () {
        Route::post('/cases/{id}/transition', [CaseController::class, 'transition'])->name('cases.transition');
        Route::post('/cases/{id}/note', [CaseController::class, 'addNote'])->name('cases.note');
        Route::post('/cases/{id}/follow-up', [CaseController::class, 'addFollowUp'])->name('cases.follow-up');
        Route::post('/cases/{id}/accept', [CaseController::class, 'accept'])->name('cases.accept');
    });

    Route::post('/cases/{id}/assign', [CaseController::class, 'assign'])
        ->middleware('permission:cases.assign')
        ->name('cases.assign');

    Route::get('/people', [PeopleController::class, 'index'])
        ->middleware('permission:people.view')
        ->name('people.index');

    Route::middleware('permission:people.manage')->group(function () {
        Route::post('/people', [PeopleController::class, 'store'])->name('people.store');
        Route::post('/people/{id}/toggle-active', [PeopleController::class, 'toggleActive'])->name('people.toggle-active');
        Route::post('/people/{id}/helper-status', [PeopleController::class, 'updateHelperStatus'])->name('people.helper-status');
        Route::put('/people/{id}', [PeopleController::class, 'update'])->name('people.update');
    });

    Route::post('/people/permissions', [PeopleController::class, 'syncPermissions'])
        ->middleware('permission:roles.manage')
        ->name('people.permissions');

    Route::middleware('permission:content.view')->group(function () {
        Route::get('/content', [ContentModuleController::class, 'index'])->name('content.index');
        Route::get('/content/{module}', [ContentModuleController::class, 'showModule'])->name('content.module');
    });

    Route::middleware('permission:modules.manage')->group(function () {
        Route::post('/content/modules', [ContentModuleController::class, 'storeRegistry'])->name('content.modules.store');
        Route::put('/content/modules/{id}', [ContentModuleController::class, 'updateRegistry'])->name('content.modules.update');
        Route::delete('/content/modules/{id}', [ContentModuleController::class, 'destroyRegistry'])->name('content.modules.destroy');
    });

    Route::middleware('permission:content.manage')->group(function () {
        Route::post('/content/{module}', [ContentModuleController::class, 'storeModuleItem'])->name('content.store');
        Route::put('/content/{module}/{id}', [ContentModuleController::class, 'updateModuleItem'])->name('content.update');
        Route::delete('/content/{module}/{id}', [ContentModuleController::class, 'destroyModuleItem'])->name('content.destroy');
        Route::post('/content/{module}/{id}/toggle', [ContentModuleController::class, 'toggleModuleItem'])->name('content.toggle');
    });

    Route::middleware('permission:cms.manage')->group(function () {
        Route::get('/pages', [StaticPageController::class, 'index'])->name('pages.index');
        Route::put('/pages/{id}', [StaticPageController::class, 'update'])->name('pages.update');
    });

    Route::get('/audit', [AuditLogController::class, 'index'])
        ->middleware('permission:audit.view')
        ->name('audit.index');

    Route::middleware('permission:theme.manage')->group(function () {
        Route::get('/theme', [ThemeController::class, 'index'])->name('theme.index');
        Route::post('/theme/publish', [ThemeController::class, 'publish'])->name('theme.publish');
        Route::post('/theme/rollback/{id}', [ThemeController::class, 'rollback'])->name('theme.rollback');
    });

    Route::middleware('permission:cms.manage')->group(function () {
        Route::get('/localization', [LocalizationController::class, 'index'])->name('localization.index');
        Route::post('/localization', [LocalizationController::class, 'update'])->name('localization.update');
        Route::get('/localization/scan-missing', [LocalizationController::class, 'scanMissing'])->name('localization.scan-missing');
        Route::get('/localization/export/json/{locale}', [LocalizationController::class, 'exportJson'])->name('localization.export.json');
        Route::get('/localization/export/csv', [LocalizationController::class, 'exportCsv'])->name('localization.export.csv');
    });

    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
        Route::post('/locations/district', [LocationController::class, 'storeDistrict'])->name('locations.district.store');
        Route::put('/locations/district/{id}', [LocationController::class, 'updateDistrict'])->name('locations.district.update');
        Route::post('/locations/taluka', [LocationController::class, 'storeTaluka'])->name('locations.taluka.store');
        Route::put('/locations/taluka/{id}', [LocationController::class, 'updateTaluka'])->name('locations.taluka.update');
        Route::post('/locations/village', [LocationController::class, 'storeVillage'])->name('locations.village.store');
        Route::put('/locations/village/{id}', [LocationController::class, 'updateVillage'])->name('locations.village.update');
        Route::post('/locations/toggle/{type}/{id}', [LocationController::class, 'toggleActive'])->name('locations.toggle');
    });

    Route::get('/settings', [SettingsController::class, 'index'])
        ->middleware('permission:settings.manage')
        ->name('settings.index');
    Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])
        ->middleware('permission:settings.manage')
        ->name('settings.general');
    Route::post('/settings/smtp', [SettingsController::class, 'updateSmtp'])
        ->middleware('permission:settings.manage')
        ->name('settings.smtp');
    Route::post('/settings/test-email', [SettingsController::class, 'testEmail'])
        ->middleware('permission:settings.manage')
        ->name('settings.test-email');
    Route::post('/settings/notifications', [SettingsController::class, 'updateNotifications'])
        ->middleware('permission:notifications.manage')
        ->name('settings.notifications');
    Route::post('/settings/notification-template/{id}/toggle', [SettingsController::class, 'toggleTemplate'])
        ->middleware('permission:notifications.manage')
        ->name('settings.template.toggle');
});
