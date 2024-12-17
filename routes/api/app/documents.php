<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\api\app\DocumentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
  Route::get('/document/information/{id}', [DocumentController::class, "information"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::get('/document/progress/{id}', [DocumentController::class, "progress"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::get('/document/scans/{id}', [DocumentController::class, "scans"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::get('/document/destination/{id}', [DocumentController::class, "destination"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::get('/documents/count', [DocumentController::class, "documentCount"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::get('/documents/{page}', [DocumentController::class, "documents"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::get('/document/{id}', [DocumentController::class, "document"])->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);
  Route::POST('/document/store', [DocumentController::class, "store"])->middleware(["hasAddPermission:" . PermissionEnum::documents->value]);
  Route::POST('/document/change-deputy', [DocumentController::class, "changeDeputy"])->middleware(["hasEditPermission:" . PermissionEnum::documents->value]);
  Route::POST('/document/update', [DocumentController::class, "update"])->middleware(["hasEditPermission:" . PermissionEnum::documents->value]);
  Route::POST('/document/recieved-from-deputy', [DocumentController::class, "recievedFromDeputy"])->middleware(["hasEditPermission:" . PermissionEnum::documents->value]);
  Route::POST('/document/recieved-from-directorate', [DocumentController::class, "recievedFromDirectorate"])->middleware(["hasEditPermission:" . PermissionEnum::documents->value]);
  Route::delete('/document/{id}', [DocumentController::class, 'destroy'])->middleware(["hasDeletePermission:" . PermissionEnum::documents->value]);
});
