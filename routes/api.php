<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;

// ── Public routes (no auth required) ──────────────────────
Route::prefix('v1')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/register',         [AuthController::class, 'register']);
        Route::post('/login',            [AuthController::class, 'login']);
        Route::post('/forgot-password',  [PasswordResetController::class, 'forgot']);
        Route::post('/reset-password',   [PasswordResetController::class, 'reset']);
        Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->name('verification.verify');
    });

    // ── Protected routes (Sanctum token required) ─────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('/logout',              [AuthController::class, 'logout']);
            Route::get('/me',                   [AuthController::class, 'me']);
            Route::post('/resend-verification', [EmailVerificationController::class, 'resend']);
            
        });

    });

});