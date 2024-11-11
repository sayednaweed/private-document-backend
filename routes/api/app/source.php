<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\api\app\SourceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
  Route::post('/source/store', [SourceController::class, "store"])->middleware(["hasAddPermission:" . PermissionEnum::settings->value]);
  Route::get('/sources', [SourceController::class, "sources"])->middleware(["hasViewPermission:" . PermissionEnum::settings->value]);
  Route::delete('/source/{id}', [SourceController::class, "destroy"])->middleware(["hasDeletePermission:" . PermissionEnum::settings->value]);
  Route::get('/source/{id}', [SourceController::class, "source"])->middleware(["hasViewPermission:" . PermissionEnum::settings->value]);
  Route::post('/source/update', [SourceController::class, "update"])->middleware(["hasEditPermission:" . PermissionEnum::settings->value]);
});
