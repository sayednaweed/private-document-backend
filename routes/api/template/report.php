<?php

use App\Http\Controllers\api\app\ReportController;
use Illuminate\Support\Facades\Route;


// Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {

Route::prefix('v1')->group(function () {
  
Route::get('/report/contains', [ReportController::class, "reportContain"]);


});