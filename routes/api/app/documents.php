<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\api\app\DocumentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
  Route::get('/documents/{page}', [DocumentController::class, "documents"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::get('/document/load/{id}', [DocumentController::class, "document"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::POST('/document/store', [DocumentController::class, "store"])->middleware(["hasAddPermission:" . PermissionEnum::documents->value]);
});
