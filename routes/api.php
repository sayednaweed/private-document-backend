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

require __DIR__ . '/api/auth/auth.php';
require __DIR__ . '/api/template/user.php';
require __DIR__ . '/api/template/job.php';
require __DIR__ . '/api/template/role.php';
require __DIR__ . '/api/template/permission.php';
require __DIR__ . '/api/template/media.php';
require __DIR__ . '/api/template/notification.php';
require __DIR__ . '/api/template/profile.php';
require __DIR__ . '/api/template/application.php';
require __DIR__ . '/api/template/log.php';
require __DIR__ . '/api/template/destination.php';
require __DIR__ . '/api/template/destinationType.php';
require __DIR__ . '/api/template/audit.php';
require __DIR__ . '/api/template/dashboard.php';
require __DIR__ . '/api/app/documents.php';
require __DIR__ . '/api/app/documentType.php';
require __DIR__ . '/api/app/source.php';
require __DIR__ . '/api/app/status.php';
require __DIR__ . '/api/app/urgency.php';
