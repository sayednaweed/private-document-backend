<?php

use App\Http\Controllers\api\app\DocumentController;
use App\Http\Controllers\api\app\SourceController;
use App\Http\Controllers\api\app\UrgencyController;
use App\Http\Controllers\api\app\StatusController;
use App\Http\Controllers\api\app\DestinationTypeController;
use App\Http\Controllers\api\app\DestinationController;
use App\Http\Controllers\api\app\DocumentTypeController;




Route::get('/document/load', [DocumentController::class, "index"]);

Route::POST('/document/store', [DocumentController::class, "store"]);




Route::get('/document/type/load', [DocumentTypeController::class, "index"]);

Route::POST('/document/type/store', [DocumentTypeController::class, "store"]);



Route::get('/document/source/load',[SourceController::class, 'index']);

Route::POST('/document/source/store',[SourceController::class, 'store']);




Route::get('/document/urgency/load',[UrgencyController::class, 'index']);

Route::POST('/document/urgency/store',[UrgencyController::class, 'store']);





Route::get('/document/status/load',[StatusController::class, 'index']);

Route::POST('/document/status/store',[StatusController::class, 'store']);


Route::get('/document/desType/load',[DestinationTypeController::class, 'index']);

Route::POST('/document/desType/store',[DestinationTypeController::class, 'store']);


Route::get('/document/destination/load',[DestinationController::class, 'index']);

Route::POST('/document/destination/store',[DestinationController::class, 'store']);



Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
 

  // ->middleware(["hasViewPermission:" . PermissionEnum::documents->value]);



});
