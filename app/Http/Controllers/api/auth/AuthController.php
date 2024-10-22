<?php

namespace App\Http\Controllers\api\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Department;
use App\Models\Email;
use App\Models\ModelJob;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class AuthController extends Controller
{
    public function user(Request $request)
    {
        $user = $request->user()->load(['contact', 'email', 'role', 'job', 'department']);
        $userPermissions = $this->userWithPermission($user);

        return response()->json(array_merge([
            "user" => [
                "id" => $user->id,
                "fullName" => $user->full_name,
                "username" => $user->username,
                'email' => $user->email ? $user->email->value : null,
                "profile" => $user->profile,
                "status" => $user->status,
                "grantPermission" => $user->grant_permission,
                "role" => ["role" => $user->role->id, "name" => $user->role->name],
                'contact' => $user->contact ? $user->contact->value : null,
                "department" => $this->getTranslationWithNameColumn($user->department, Department::class),
                "job" => $this->getTranslationWithNameColumn($user->job, ModelJob::class),
                "createdAt" => $user->created_at,
            ]
        ], [
            "permissions" => $userPermissions["permissions"],
        ]), 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        try {
            $email = Email::where('value', '=', $credentials['email'])->first();
            $user = User::where('email_id', '=', $email->id)->first();
            if ($user) {
                if ($user->status == 0)
                    return response()->json(['message' => "Your account is locked!"], 403);
                // Check password
                if (!Hash::check($credentials['password'], $user->password)) {
                    return response()->json([
                        "message" => "Provided credentials are incorrect"
                    ], 422);
                }
                $token = $user->createToken("web")->plainTextToken;
                $userPermissions = $this->userWithPermission($user);
                $user = $user->load(['contact', 'role']);
                return  array_merge([
                    "user" => [
                        "id" => $user->id,
                        "fullName" => $user->full_name,
                        "username" => $user->username,
                        'email' => $user->email ? $user->email->value : null,
                        "profile" => $user->profile,
                        "status" => $user->status,
                        "grantPermission" => $user->grant_permission,
                        "role" => ["role" => $user->role->id, "name" => $user->role->name],
                        'contact' => $user->contact ? $user->contact->value : null,
                        "department" => $this->getTranslationWithNameColumn($user->department, Department::class),
                        "job" => $this->getTranslationWithNameColumn($user->job, ModelJob::class),
                        "createdAt" => $user->created_at,
                    ]
                ], [
                    "token" => $token,
                    "permissions" => $userPermissions["permissions"],
                ]);
            }
            return response()->json([
                "message" => "User not found!"
            ], 404);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json(['message' => "Something went wrong please try again later!"], 500);
        }
    }
    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $request->user()->currentAccessToken()->delete();

        return response(['message' => 'User logout successfully.'], 204);
    }
    // HELPER
    protected function userWithPermission($user)
    {
        $userId = $user->id;
        $userPermissions = DB::table('user_permissions')
            ->join('permissions', function ($join) use ($userId) {
                $join->on('user_permissions.permission', '=', 'permissions.name')
                    ->where('user_permissions.user_id', '=', $userId);
            })
            ->select(
                "permissions.name as permission",
                "permissions.icon as icon",
                "permissions.priority as priority",
                "user_permissions.view",
                "user_permissions.add",
                "user_permissions.delete",
                "user_permissions.edit",
                "user_permissions.id",
            )
            ->orderBy("priority")
            ->get();
        return ["user" => $user->toArray(), "permissions" => $userPermissions];
    }
}
