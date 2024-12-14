
<?php

use App\Http\Controllers\api\template\MediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
    Route::get('/media/{storage}/{folder}/{filename}', [MediaController::class, "show"]);
    Route::get('/media/{storage}/{folder}/{folderType}/{filename}', [MediaController::class, "downloadDoc"]);
});
