
<?php

use App\Http\Controllers\api\template\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/dashboard/info', [DashboardController::class, 'dashboardInfo']);
});
