<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Models\Contact;
use App\Models\Translate;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\UserPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

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
        $userCount = User::count();
        $todayCount = User::whereDate('created_at', Carbon::today())->count();
        $activeUserCount = User::where('status', true)->count();
        $inActiveUserCount = User::where('status', false)->count();
        return response()->json([
            'counts' => [
                "active" => $userCount,
                "inActive" => $todayCount,
                "total" => $activeUserCount,
                "todayTotal" => $inActiveUserCount
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE);


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
