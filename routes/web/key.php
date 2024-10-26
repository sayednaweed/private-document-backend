<?php

use App\Http\Controllers\web\generator\ApiKeyController;
use Illuminate\Support\Facades\Route;




Route::middleware(['isSuper'])->group(function () {
    Route::get('user/master/dashboard', [ApiKeyController::class, 'index'])->name('master.dashboard');

    Route::get('user/master/key', [ApiKeyController::class, 'key'])
        ->name('master.key');

    Route::POST('user/master/key/store', [ApiKeyController::class, 'store'])
        ->name('master.key.store');

    Route::get('user/master/key/load', [ApiKeyController::class, 'load'])
        ->name('master.key.index');

    Route::get('user/master/key/edit', [ApiKeyController::class, 'edit'])
        ->name('master.key.edit');

    Route::POST('user/master/key/revoke', [ApiKeyController::class, 'revoke'])
        ->name('master.key.revoke');
});
