<?php

namespace App\Http\Middleware\api\template;

use App\Enums\RoleEnum;
use App\Models\User;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AccessUserCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $authUser = $request->user();
            $paramId = $request->route('id');
            $userId = $paramId ? $paramId : $request->id;
            // 1. It is super user do not allow access
            if ($userId == "1") {
                return response()->json([
                    'message' => __('app_translation.unauthorized'),
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }
            // 2. Do not allow admin user data if login user is not super
            $user = User::find($userId);
            if ($user) {
                if (
                    $user->role_id === RoleEnum::admin->value
                    && $authUser->id != "1"
                ) {
                    return response()->json([
                        'message' => __('app_translation.unauthorized'),
                    ], 403, [], JSON_UNESCAPED_UNICODE);
                } else {
                    $request->attributes->set('validatedUser', $user);
                    return $next($request);
                }
            } else {
                return response()->json([
                    'message' => __('app_translation.failed'),
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $err) {
            Log::info('ModifyUserCheckMiddleware error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
