
<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\api\auditLogController;
use Illuminate\Support\Facades\Route;

    Route::get('/audit/logs/{page}', [auditLogController::class, "audits"]);
    Route::get('/audit/log/{id}', [auditLogController::class, "audit"]);
    // ->middleware(["hasViewPermission:" . PermissionEnum::users->value]);

Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
  


});
