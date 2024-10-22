<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::middleware('api.key')->group(function () {
    Route::get('/protected-resource', function () {
        return response()->json(['message' => 'You have access!']);
    });
});

require __DIR__ . '/api/auth.php';
require __DIR__ . '/api/user.php';
require __DIR__ . '/api/job.php';
require __DIR__ . '/api/department.php';
require __DIR__ . '/api/role.php';
require __DIR__ . '/api/permission.php';
require __DIR__ . '/api/media.php';
require __DIR__ . '/api/notification.php';
require __DIR__ . '/api/profile.php';
require __DIR__ . '/api/application.php';
// require __DIR__ . '/api/log.php';
