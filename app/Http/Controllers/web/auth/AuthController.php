<?php

namespace App\Http\Controllers\web\auth;

use App\Traits\WebAuth\WebAuthTrait;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use WebAuthTrait;


    public function webauthintcation(Request $req)
    {
        return $this->weblogin($req);
    }
    public function weblogout(Request $request): RedirectResponse
    {
        // Perform logout logic here
        Auth::logout();

        // Optionally clear session data
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to the login page or another route
        return redirect()->route('web.login')->with('message', 'Logged out successfully.');
    }
}
