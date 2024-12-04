<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\api\app\DocumentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
  Route::get('/document/information/{id}', [DocumentController::class, "information"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::get('/documents/count', [DocumentController::class, "documentCount"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::get('/documents/{page}', [DocumentController::class, "documents"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::get('/document/{id}', [DocumentController::class, "document"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::POST('/document/store', [DocumentController::class, "store"])->middleware(["hasAddPermission:" . PermissionEnum::documents->value]);
  Route::delete('/document/{id}', [DocumentController::class, 'destroy'])->middleware(["hasDeletePermission:" . PermissionEnum::documents->value]);
});
