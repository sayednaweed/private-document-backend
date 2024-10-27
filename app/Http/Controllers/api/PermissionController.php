<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use App\Models\UserPermission;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    public function permissions($id, Request $request)
    {
        $userPermissions = [];
        try {
            $user = $request->user();
            if ($user->grant_permission) {
                $userPermissions = RolePermission::where("role", '=', $id)->select("permission as name")->get();
            } else {
                return response()->json([
                    'message' => __('app_translation.unauthorized'),
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }

            return response()->json($userPermissions, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
