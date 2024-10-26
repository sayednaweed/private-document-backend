<?php

namespace App\Traits\WebAuth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Enums\RoleEnum;
use Illuminate\Http\RedirectResponse;

trait WebAuthTrait
{
    public function weblogin($request)
    {
        $validator = validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);
        if ($validator->passes()) {
            if (Auth::guard('web')->attempt([
                'username' => $request->email,
                'password' => $request->password
            ], $request->get('remember'))) {
                $role =  Auth::user()->role_id;
                if ($role == RoleEnum::super->value) {

                    // return view('keygenerator.generatekey',['role'=>$role]);

                    return redirect()->route('master.dashboard')->with($role);
                    // return 

                } else {
                    Auth::guard('user')->logout();
                    return redirect()->route('web.login')->with(
                        'error',
                        'Email/Password is not authorize'
                    );
                }
            } else {
                return redirect()->route('web.login')->with(
                    'error',
                    'Email/Password is incorrect'
                );
            }
        } else {
            return redirect()->route('UserLogin')
                ->withErrors($validator)
                ->withInput($request->only('email'));
            // return "error";
        }
    }


    public function traitweblogout($request): RedirectResponse
    {

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
