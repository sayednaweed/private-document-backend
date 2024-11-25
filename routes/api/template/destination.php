<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\api\template\DestinationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
  Route::post('/destination/store', [DestinationController::class, "store"])->middleware(["hasAddPermission:" . PermissionEnum::settings->value]);
  Route::get('/destinations', [DestinationController::class, "destinations"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::get('/muqams', [DestinationController::class, "muqams"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::get('/directorates', [DestinationController::class, "directorates"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::delete('/destination/{id}', [DestinationController::class, "destroy"])->middleware(["hasDeletePermission:" . PermissionEnum::settings->value]);
  Route::get('/destination/{id}', [DestinationController::class, "destination"])->middleware(["hasViewPermission:" . PermissionEnum::settings->value]);
  Route::post('/destination/update', [DestinationController::class, "update"])->middleware(["hasEditPermission:" . PermissionEnum::settings->value]);
});
