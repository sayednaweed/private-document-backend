<?php

use App\Http\Controllers\api\app\DestinationController;





Route::get('/document/destination/load',[DestinationController::class, 'destinations']);

Route::POST('/document/destination/store',[DestinationController::class, 'store']);



Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
 

  // ->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);



});
