<?php

use App\Http\Controllers\Api\V1\ApplicationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\HelperController;
use App\Http\Controllers\Api\V1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ==========================================
    // 1. PUBLIC CONFIGURATION
    // ==========================================
    Route::prefix('config')->group(function () {
        Route::get('/theme', [ConfigController::class, 'theme']);
        Route::get('/languages', [ConfigController::class, 'languages']);
        Route::get('/translations', [ConfigController::class, 'translations']);
        Route::get('/home-tiles', [ConfigController::class, 'homeTiles']);
        Route::get('/master-data', [ConfigController::class, 'masterData']);
    });

    // Public Master Data & Tracking
    Route::get('/categories', [ConfigController::class, 'categories']);
    Route::get('/districts', [ConfigController::class, 'districts']);
    Route::get('/applications/track/{caseNo}', [ApplicationController::class, 'track']);
    Route::post('/applications', [ApplicationController::class, 'store']);

    // ==========================================
    // 2. AUTHENTICATION (OTP FLOW)
    // ==========================================
    Route::prefix('auth')->group(function () {
        Route::post('/otp/request', [AuthController::class, 'requestOtp']);
        Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);
    });

    // ==========================================
    // 3. AUTHENTICATED ROUTES (SANCTUM)
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {

        // User & Device
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/me', [AuthController::class, 'updateMe']);
        Route::post('/devices', [AuthController::class, 'registerDevice']);

        // Applications & Case Workflow
        Route::get('/applications', [ApplicationController::class, 'index']);
        Route::post('/applications', [ApplicationController::class, 'store']);
        Route::get('/applications/{id}', [ApplicationController::class, 'show']);
        Route::get('/applications/{id}/timeline', [ApplicationController::class, 'timeline']);
        Route::post('/applications/{id}/documents', [ApplicationController::class, 'uploadDocument']);
        Route::get('/applications/{id}/messages', [ApplicationController::class, 'messages']);
        Route::post('/applications/{id}/messages', [ApplicationController::class, 'sendMessage']);
        Route::post('/applications/{id}/reopen', [ApplicationController::class, 'reopen']);
        Route::post('/applications/{id}/feedback', [ApplicationController::class, 'feedback']);

        // Helper Portal (Staff, Mentors, Volunteers)
        Route::prefix('helper')->group(function () {
            Route::get('/dashboard', [HelperController::class, 'dashboard']);
            Route::get('/cases', [HelperController::class, 'cases']);
            Route::post('/cases/{id}/accept', [HelperController::class, 'acceptCase']);
            Route::post('/cases/{id}/decline', [HelperController::class, 'declineCase']);
            Route::post('/cases/{id}/status', [HelperController::class, 'updateStatus']);
            Route::post('/cases/{id}/note', [HelperController::class, 'addNote']);
            Route::post('/cases/{id}/request-info', [HelperController::class, 'requestInfo']);
            Route::post('/cases/{id}/follow-up', [HelperController::class, 'scheduleFollowUp']);
            Route::post('/cases/{id}/resolve', [HelperController::class, 'resolve']);
        });

        // Content Modules
        Route::get('/schemes', [ContentController::class, 'schemes']);
        Route::post('/schemes/match', [ContentController::class, 'matchSchemes']);
        Route::get('/education/libraries', [ContentController::class, 'libraries']);
        Route::get('/education/scholarships', [ContentController::class, 'scholarships']);
        Route::get('/education/mock-tests', [ContentController::class, 'mockTests']);
        Route::get('/education/mock-tests/{id}', [ContentController::class, 'mockTestDetail']);
        Route::get('/education/study-materials', [ContentController::class, 'studyMaterials']);
        Route::get('/mentors', [ContentController::class, 'mentors']);
        Route::post('/mentors/{id}/ask', [ContentController::class, 'askMentor']);
        Route::get('/jobs', [ContentController::class, 'jobs']);
        Route::get('/entrepreneur/ideas', [ContentController::class, 'businessIdeas']);
        Route::get('/family-support', [ContentController::class, 'familySupport']);
        Route::post('/family-support/{id}/donate', [ContentController::class, 'donate']);
        Route::get('/health/camps', [ContentController::class, 'healthCamps']);
        Route::get('/health/hospitals', [ContentController::class, 'hospitals']);
        Route::get('/health/blood-requests', [ContentController::class, 'bloodRequests']);
        Route::get('/sakhi', [ContentController::class, 'sakhi']);
        Route::get('/village-reports', [ContentController::class, 'villageReports']);
        Route::post('/village-reports', [ContentController::class, 'storeVillageReport']);
        Route::get('/green/plantations', [ContentController::class, 'plantations']);
        Route::post('/green/plantations', [ContentController::class, 'storePlantation']);
        Route::post('/volunteer/signup', [ContentController::class, 'volunteerSignup']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::post('/notifications/receipt', [NotificationController::class, 'receipt']);
    });
});
