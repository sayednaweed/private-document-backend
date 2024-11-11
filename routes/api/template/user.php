
<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\api\template\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
    Route::get('/users/record/count', [UserController::class, "userCount"]);
    Route::get('/users/{page}', [UserController::class, "users"])->middleware(["hasViewPermission:" . PermissionEnum::users->value]);
    Route::get('/user/{id}', [UserController::class, "user"])->middleware(['accessUserCheck', "hasViewPermission:" . PermissionEnum::users->value]);
    Route::post('/user/change-password', [UserController::class, 'changePassword'])->middleware(['accessUserCheck', "hasEditPermission:" . PermissionEnum::users->value]);
    Route::delete('/user/delete-profile/{id}', [UserController::class, 'deleteProfile'])->middleware(["hasDeletePermission:" . PermissionEnum::users->value]);
    Route::post('/user/update-profile', [UserController::class, 'updateProfile'])->middleware(["hasEditPermission:" . PermissionEnum::users->value]);
    Route::post('/user/update', [UserController::class, 'update'])->middleware(["hasEditPermission:" . PermissionEnum::users->value, 'accessUserCheck']);
    Route::post('/user/store', [UserController::class, 'store'])->middleware(["hasAddPermission:" . PermissionEnum::users->value]);
    Route::delete('/user/{id}', [UserController::class, 'destroy'])->middleware(["hasDeletePermission:" . PermissionEnum::users->value]);
    Route::post('/user/update/permission', [UserController::class, 'updatePermission'])->middleware(['hasGrantPermission', "hasEditPermission:" . PermissionEnum::users->value]);
    Route::post('/user/validate/email/contact', [UserController::class, "validateEmailContact"]);
});
