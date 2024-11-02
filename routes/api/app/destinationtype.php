<?php

use App\Http\Controllers\api\app\DestinationTypeController;


Route::get('/document/desType/load',[DestinationTypeController::class, 'destinationTypes']);

Route::POST('/document/desType/store',[DestinationTypeController::class, 'store']);



Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
 

  // ->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);



});
