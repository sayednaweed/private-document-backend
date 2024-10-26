<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Translate;
use App\Models\User;
use App\Models\UserPermission;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestController extends Controller
{
    public function index(Request $request)
    {
        // $path = storage_path('app/' . "images/a7453951-0348-48b7-a7ab-e75916e20a63.jpg");

        // if (!Storage::disk('local')->exists("images/a7453951-0348-48b7-a7ab-e75916e20a63.jpg")) {
        //     return response()->json("Not found");
        // }

        // Session::put('locale', "fa");
        // $sessionLocale = Session::get('locale');

        // return $sessionLocale;
        $user = User::create([
            'full_name' => 'Sayed Naweed Sayedy',
            'username' => 'super@admin.com',
            'email_id' =>  1,
            'password' =>  Hash::make("123123123"),
            'status' =>  true,
            'grant_permission' =>  true,
            'role_id' =>  RoleEnum::super->value,
            'contact_id' =>  null,
            'job_id' =>  1,
            'department_id' =>  1,
        ]);
        return dd($user);


        // $foundUser = User::with(['permissions', 'contact', 'email', 'userRole', 'userJob', 'userDepartment'])
        //     ->select(
        //         "id",
        //         "full_name as fullName",
        //         "username",
        //         "profile",
        //         "status",
        //         "grant_permission as grantPermission",
        //         "email_id",
        //         "role",
        //         "contact_id",
        //         "job_id",
        //         "department_id",
        //         "created_at as createdAt",
        //     )->find("11");

        // $authUser = User::with(['permissions'])->find("1");;
        // // Combine permissions of user1 and user2
        // $combinedPermissions = $foundUser->permissions->concat($authUser->permissions)->unique('permission');
        // return $combinedPermissions;

        // $user = User::find(10);
        // $userId = $user->id;
        // $userPermissions = DB::table('user_permissions')
        //     ->join('permissions', function ($join) use ($userId) {
        //         $join->on('user_permissions.permission', '=', 'permissions.name')
        //             ->where('user_permissions.user_id', '=', $userId);
        //     })
        //     ->select(
        //         "permissions.name as permission",
        //         "permissions.icon as icon",
        //         "permissions.priority as priority",
        //         "user_permissions.view",
        //         "user_permissions.add",
        //         "user_permissions.delete",
        //         "user_permissions.edit",
        //         "user_permissions.id",
        //     )
        //     ->orderBy("priority")
        //     ->get();
        // return ["user" => $user->toArray(), "permissions" => $userPermissions];
    }
}
