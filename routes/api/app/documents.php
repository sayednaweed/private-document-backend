<?php

use App\Http\Controllers\api\app\DocumentController;


Route::get('/document/load', [DocumentController::class, "documents"]);

Route::POST('/document/store', [DocumentController::class, "store"]);


Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
 

  // ->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);



});
