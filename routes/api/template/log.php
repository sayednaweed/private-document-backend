
<?php

use App\Http\Controllers\api\template\LogController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware("auth:sanctum")->group(function () {
    Route::get('/file-logs', [LogController::class, "fileLogs"]);
    Route::get('/database-logs', [LogController::class, "databaseLogs"]);
    Route::post('/logs/clear', [LogController::class, "clear"]);
});
