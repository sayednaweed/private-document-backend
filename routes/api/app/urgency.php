<?php

use App\Http\Controllers\api\app\UrgencyController;





Route::get('/document/urgency/load',[UrgencyController::class, 'urgencies']);

Route::POST('/document/urgency/store',[UrgencyController::class, 'store']);




Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
 

  // ->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);



});
