<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Learner\CourseController;
use App\Http\Controllers\Learner\LessonController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\DashboardController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;




// ── Public routes (no auth required) ──────────────────────
Route::prefix('v1')
->middleware([EnsureFrontendRequestsAreStateful::class])
->group(function () {

   
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/register',         [AuthController::class, 'register']);
        Route::post('/login',            [AuthController::class, 'login']);
        Route::post('/forgot-password',  [PasswordResetController::class, 'forgot']);
        Route::post('/reset-password',   [PasswordResetController::class, 'reset']);
        Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
            ->name('verification.verify');
    });

    // Courses - public (no auth required for catalog)
    Route::prefix('courses')->group(function () {
        Route::get('/',          [CourseController::class, 'index']);
        Route::get('/{id}',      [CourseController::class, 'show']);
    });

    // ── Protected routes (Sanctum token required) ─────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('/logout',              [AuthController::class, 'logout']);
            Route::get('/me',                   [AuthController::class, 'me']);
            Route::post('/resend-verification', [EmailVerificationController::class, 'resend']);
            
        });

        // ── Learner routes ─────────────────────────────────────
        Route::prefix('lessons')->group(function () {
            Route::get('/{id}',          [LessonController::class, 'show']);
            Route::post('/{id}/complete',[LessonController::class, 'complete']);
        });


        // ── Gated routes — require specific plan ───────────────
        Route::middleware('feature:mock_exams')->group(function () {
            // mock exam routes go here in Phase 2
        });

        Route::middleware('feature:assign_tests')->group(function () {
            // institution assign test routes go here in Phase 4
        });

        Route::middleware('feature:bulk_export')->group(function () {
            // institution bulk export routes go here in Phase 4
        });


                // Flashcards
        Route::get('/flashcards',             [FlashcardController::class, 'index']);
        Route::get('/flashcards/due',         [FlashcardController::class, 'due']);
        Route::post('/flashcards/{id}/rate',  [FlashcardController::class, 'rate']);

        // Tests
        Route::get('/tests',                                   [TestController::class, 'index']);
        Route::post('/tests/{testSetId}/start',                [TestController::class, 'start']);
        Route::post('/tests/attempts/{attemptId}/answer',      [TestController::class, 'answer']);
        Route::post('/tests/attempts/{attemptId}/submit',      [TestController::class, 'submit']);
        Route::get('/tests/attempts/{attemptId}/results',      [TestController::class, 'results']);

        // Dashboard (real data)
        Route::get('/dashboard',  [DashboardController::class, 'index']);
            });

});