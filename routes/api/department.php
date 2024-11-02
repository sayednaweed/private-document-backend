
<?php

use App\Http\Controllers\api\DepartmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
    Route::get('/departments', [DepartmentController::class, "departments"])->middleware(['allowAdminOrSuper']);
    Route::delete('/department/{id}', [DepartmentController::class, "destroy"])->middleware(['allowAdminOrSuper']);
    Route::get('/department/{id}', [DepartmentController::class, "department"])->middleware(['allowAdminOrSuper']);
    // Route::post('/department/store', [DepartmentController::class, "store"])->middleware(['allowAdminOrSuper']);
    Route::post('/department/update', [DepartmentController::class, "update"])->middleware(['allowAdminOrSuper']);
});

Route::post('/department/store', [DepartmentController::class, "store"]);
