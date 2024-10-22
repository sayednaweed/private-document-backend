
<?php

use App\Http\Controllers\api\RoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
    Route::get('/roles', [RoleController::class, "roles"])->middleware(['isAdminOrSuper']);
    Route::delete('/role/delete', [RoleController::class, "delete"])->middleware(['isAdminOrSuper']);
    Route::post('/role/store', [RoleController::class, "store"])->middleware(['isAdminOrSuper']);
    Route::put('/role/update', [RoleController::class, "update"])->middleware(['isAdminOrSuper']);
});
