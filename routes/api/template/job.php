
<?php

use App\Http\Controllers\api\template\JobController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
    Route::get('/jobs', [JobController::class, "jobs"])->middleware(['allowAdminOrSuper']);
    Route::delete('/job/{id}', [JobController::class, "destroy"])->middleware(['allowAdminOrSuper']);
    Route::get('/job/{id}', [JobController::class, "job"])->middleware(['allowAdminOrSuper']);
    Route::post('/job/store', [JobController::class, "store"])->middleware(['allowAdminOrSuper']);
    Route::post('/job/update', [JobController::class, "update"])->middleware(['allowAdminOrSuper']);
});
