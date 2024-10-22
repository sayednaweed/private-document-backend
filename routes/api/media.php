
<?php

use App\Http\Controllers\api\MediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
    Route::get('/{storage}/{folder}/{filename}', [MediaController::class, "show"]);
});
