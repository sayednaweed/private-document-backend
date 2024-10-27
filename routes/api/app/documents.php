<?php

use App\Http\Controllers\api\app\DocumentController;



Route::get('/document/load', [DocumentController::class, "index"]);


Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
 

  // ->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);



});
