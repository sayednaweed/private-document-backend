<?php

namespace App\Http\Controllers\api\template;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Exception;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function roles()
    {
        try {
            $excludedIds = [RoleEnum::super->value];
            return response()->json(Role::whereNotIn('id', $excludedIds)->select("name", 'id', 'created_at as createdAt')->get());
        } catch (Exception $err) {
            Log::info('User login error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
