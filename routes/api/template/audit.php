<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\api\template\AuditLogController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->middleware(['api.key', "auth:sanctum"])->group(function () {
    Route::get('/audits/{page}', [AuditLogController::class, "audits"])->middleware(["hasViewPermission:" . PermissionEnum::audit->value]);
});
