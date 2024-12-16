<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\api\app\DocumentTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
  Route::post('/document-type/store', [DocumentTypeController::class, "store"])->middleware(["hasAddPermission:" . PermissionEnum::settings->value]);
  Route::get('/document-types', [DocumentTypeController::class, "documentTypes"]);
  Route::delete('/document-type/{id}', [DocumentTypeController::class, "destroy"])->middleware(["hasDeletePermission:" . PermissionEnum::settings->value]);
  Route::get('/document-type/{id}', [DocumentTypeController::class, "documentType"])->middleware(["hasViewPermission:" . PermissionEnum::settings->value]);
  Route::post('/document-type/update', [DocumentTypeController::class, "update"])->middleware(["hasEditPermission:" . PermissionEnum::settings->value]);
});
