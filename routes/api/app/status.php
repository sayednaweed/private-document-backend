
<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\api\app\StatusController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
    Route::post('/status/store', [StatusController::class, "store"])->middleware(["hasAddPermission:" . PermissionEnum::settings->value]);
    Route::get('/statuses', [StatusController::class, "statuses"])->middleware(["hasViewPermission:" . PermissionEnum::settings->value]);
    Route::delete('/status/{id}', [StatusController::class, "destroy"])->middleware(["hasDeletePermission:" . PermissionEnum::settings->value]);
    Route::get('/status/{id}', [StatusController::class, "status"])->middleware(["hasViewPermission:" . PermissionEnum::settings->value]);
    Route::post('/status/update', [StatusController::class, "update"])->middleware(["hasEditPermission:" . PermissionEnum::settings->value]);
});
