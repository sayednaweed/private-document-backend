
<?php

use App\Http\Controllers\api\template\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/profile/picture-update', [ProfileController::class, 'updatePicture']);
    Route::delete('/profile/picture-delete', [ProfileController::class, 'deletePicture']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
});
