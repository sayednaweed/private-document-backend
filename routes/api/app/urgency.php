<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\api\app\UrgencyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
  Route::post('/urgency/store', [UrgencyController::class, "store"])->middleware(["hasAddPermission:" . PermissionEnum::settings->value]);
  Route::get('/urgencies', [UrgencyController::class, "urgencies"]);
  Route::delete('/urgency/{id}', [UrgencyController::class, "destroy"])->middleware(["hasDeletePermission:" . PermissionEnum::settings->value]);
  Route::get('/urgency/{id}', [UrgencyController::class, "urgency"])->middleware(["hasViewPermission:" . PermissionEnum::settings->value]);
  Route::post('/urgency/update', [UrgencyController::class, "update"])->middleware(["hasEditPermission:" . PermissionEnum::settings->value]);
});
