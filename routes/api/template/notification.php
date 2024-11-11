
<?php

use App\Http\Controllers\api\template\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
    Route::get('/notifications', [NotificationController::class, "notifications"]);
    Route::delete('/notification-delete/{id}', [NotificationController::class, "delete"]);
    Route::post('/notification-update', [NotificationController::class, "update"]);
});
