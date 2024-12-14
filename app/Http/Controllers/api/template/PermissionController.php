<?php

namespace App\Http\Controllers\api\template;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function permissions($id, Request $request)
    {
        $userPermissions = [];
        $user = $request->user();
        if ($user->grant_permission) {
            $userPermissions = RolePermission::where("role", '=', $id)->select("permission as name")->get();
        } else {
            return response()->json([
                'message' => __('app_translation.unauthorized'),
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json(
            $userPermissions,
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }
}
