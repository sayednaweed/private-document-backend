<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function roles(Request $request)
    {
        try {
            $user = $request->user();
            if ($this->isAdminOrSuper($user))
                return response()->json(Role::select("name", 'id', 'created_at as createdAt')->get());
            else
                return response()->json([
                    'message' => __('app_translation.unauthorized'),
                ], 403, [], JSON_UNESCAPED_UNICODE);
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
