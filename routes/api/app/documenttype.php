<?php

use App\Http\Controllers\api\app\DocumentTypeController;





Route::get('/document/type/load', [DocumentTypeController::class, "documentTypes"]);

Route::POST('/document/type/store', [DocumentTypeController::class, "store"]);




Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
 

  // ->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);



});
