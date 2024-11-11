<?php

namespace App\Http\Middleware\api\template;

use App\Enums\RoleEnum;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AllowAdminOrSuperMiddleware
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
            if (
                $authUser->role_id === RoleEnum::admin->value
                || $authUser->role_id === RoleEnum::super->value
            ) {
                return $next($request);
            } else {
                return response()->json([
                    'message' => __('app_translation.unauthorized'),
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $err) {
            Log::info('ModifyUserCheckMiddleware error =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
