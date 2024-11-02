<?php


use App\Http\Controllers\api\app\SourceController;





Route::get('/document/source/load',[SourceController::class, 'sources']);

Route::POST('/document/source/store',[SourceController::class, 'store']);



Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
 

  // ->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);



});
