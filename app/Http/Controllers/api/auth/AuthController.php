<?php

namespace App\Http\Controllers\api\auth;

use App\Enums\LanguageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Destination;
use App\Models\Email;
use App\Models\ModelJob;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function user(Request $request)
    {
        $user = $request->user()->load([
            'contact:id,value',
            'email:id,value',
            'role:id,name',
        ]);
        $userPermissions = $this->userWithPermission($user);

        return response()->json(array_merge([
            "user" => [
                "id" => $user->id,
                "fullName" => $user->full_name,
                "username" => $user->username,
                'email' => $user->email ? $user->email->value : "",
                "profile" => $user->profile,
                "status" => $user->status,
                "grantPermission" => $user->grant_permission,
                "role" => ["role" => $user->role->id, "name" => $user->role->name],
                'contact' => $user->contact ? $user->contact->value : "",
                "destination" => $user->destination ? $this->getTranslationWithNameColumn($user->destination, Destination::class) : "",
                "job" => $user->job ? $this->getTranslationWithNameColumn($user->job, ModelJob::class) : "",
                "createdAt" => $user->created_at,
            ]
        ], [
            "permissions" => $userPermissions["permissions"],
        ]), 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $email = Email::where('value', '=', $credentials['email'])->first();
        if (!$email) {
            return response()->json([
                'message' => __('app_translation.email_not_found'),
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }
        $user = User::where('email_id', '=', $email->id)->first();
        if ($user) {
            if ($user->status == 0) {
                return response()->json([
                    'message' => __('app_translation.account_is_lock'),
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }
            // Check password
            if (!Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'message' => __('app_translation.incorrect_credentials'),
                ], 422, [], JSON_UNESCAPED_UNICODE);
            }
            $token = $user->createToken("web")->plainTextToken;
            $userPermissions = $this->userWithPermission($user);
            $user = $user->load([
                'contact:id,value',
                'email:id,value',
                'role:id,name',
            ]);
            return response()->json(
                array_merge([
                    "user" => [
                        "id" => $user->id,
                        "fullName" => $user->full_name,
                        "username" => $user->username,
                        'email' => $user->email ? $user->email->value : "",
                        "profile" => $user->profile,
                        "status" => $user->status,
                        "grantPermission" => $user->grant_permission,
                        "role" => ["role" => $user->role->id, "name" => $user->role->name],
                        'contact' => $user->contact ? $user->contact->value : "",
                        "destination" => $user->destination ? $this->getTranslationWithNameColumn($user->destination, Destination::class) : "",
                        "job" => $user->job ? $this->getTranslationWithNameColumn($user->job, ModelJob::class) : "",
                        "createdAt" => $user->created_at,
                    ]
                ], [
                    "token" => $token,
                    "permissions" => $userPermissions["permissions"],
                ]),
                200,
                [],
                JSON_UNESCAPED_UNICODE
            );
        } else {
            return response()->json([
                'message' => __('app_translation.user_not_found')
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => __('app_translation.user_logged_out_success')
        ], 204, [], JSON_UNESCAPED_UNICODE);
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
