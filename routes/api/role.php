
<?php

use App\Http\Controllers\api\RoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
    Route::get('/roles', [RoleController::class, "roles"])->middleware(['allowAdminOrSuper']);
    Route::delete('/role/{id}', [RoleController::class, "destroy"])->middleware(['allowAdminOrSuper']);
    Route::post('/role/store', [RoleController::class, "store"])->middleware(['allowAdminOrSuper']);
    Route::put('/role/update', [RoleController::class, "update"])->middleware(['allowAdminOrSuper']);
    Route::get('/role/{id}', [RoleController::class, "role"])->middleware(['allowAdminOrSuper']);
});
