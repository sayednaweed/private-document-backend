<?php

use App\Http\Controllers\api\auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "guest"])->group(function () {
    Route::post('/auth-login', [AuthController::class, 'login']);
    Route::get('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
});

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
    // Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    // ->middleware('throttle:6,1')
    // ->name('verification.send');

    Route::post('/auth-logout', [AuthController::class, 'logout']);
    Route::get('/auth-user', [AuthController::class, 'user']);
    // Super user will use this route for user update
    Route::post('/auth/user/change-password', [AuthController::class, 'changePassword']);
    Route::post('/auth/user/change-permissions', [AuthController::class, 'changePermissions']);
    Route::delete('/auth/user/delete/{id}', [AuthController::class, "delete"]);
});
