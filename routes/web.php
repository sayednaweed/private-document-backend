<?php

use App\Enums\RoleEnum;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\ApplicationController;
use App\Http\Controllers\lib\report\ReportController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;




Route::get('/testing', [TestController::class, "index"]);

Route::get('/welcome', function () {
    // $user = User::find(11);

    User::create([
        'full_name' => 'waheed',
        'username' => 'master@master.com',
        'email_id' =>  1,
        'password' =>  Hash::make("123"),
        'status' => true,
        'grant_permission' => true,
        'role_id' =>  RoleEnum::super,
        'contact_id' => 1,
        'job_id' =>  1,
        'department_id' => 1,

    ]);
    return 'successfuly add user';
});



Route::prefix('v1')->group(function () {
    Route::get('/lang/{locale}', [ApplicationController::class, 'changeLocale']);
});


Route::get('/generate-pdf', [ReportController::class, 'testReport']);


require __DIR__ . '/web/auth.php';
require __DIR__ . '/web/key.php';
