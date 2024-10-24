
<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
    Route::get('/users/{page}', [UserController::class, "users"])->middleware(["hasViewPermission:" . PermissionEnum::users->value]);
    Route::get('/user/{id}', [UserController::class, "user"])->middleware(['isAdminOrSuper', "hasViewPermission:" . PermissionEnum::users->value]);
    Route::post('/user/edit-security-question', [UserController::class, "changeSecurityQuestion"]);
    Route::post('/user/account-exist', [UserController::class, "accountExist"]);
    Route::post('/user/change-password', [UserController::class, 'userChangePassword']);
    Route::delete('/user/delete-profile/{id}', [UserController::class, 'deleteProfile'])->middleware(["hasDeletePermission:" . PermissionEnum::users->value]);
    Route::post('/user/update-profile', [UserController::class, 'updateProfile'])->middleware(["hasEditPermission:" . PermissionEnum::users->value]);
    Route::post('/user/update', [UserController::class, 'update'])->middleware(["hasEditPermission:" . PermissionEnum::users->value, 'isNotSuperInfo']);
    Route::post('/user/store', [UserController::class, 'store'])->middleware(["hasViewPermission:" . PermissionEnum::users->value]);
    Route::delete('/user/{id}', [UserController::class, 'destroy'])->middleware(["hasDeletePermission:" . PermissionEnum::users->value]);
});
Route::prefix('v1')->group(function () {
    Route::post('/user/email-exist', [UserController::class, "emailExist"]);
});
