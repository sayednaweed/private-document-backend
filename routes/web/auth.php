<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\web\auth\AuthController;

// route for load the login page
Route::get('key', function () {
    return view('auth.login');
})->name('web.login');




// route for check the auth credintials
Route::POST('user/authintication', [AuthController::class, 'webauthintcation'])
    ->name('web.authintiction');

// route gor logout the auth user
Route::get('userweb/logout', [AuthController::class, 'weblogout'])
    ->name('web.logout');
