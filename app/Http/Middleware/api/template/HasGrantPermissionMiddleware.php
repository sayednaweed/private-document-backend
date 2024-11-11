<?php

namespace App\Http\Middleware\api\template;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasGrantPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = $request->user();
        if ($authUser->grant_permission) {
            return $next($request);
        }
        return response()->json([
            'message' => __('app_translation.unauthorized'),
        ], 403, [], JSON_UNESCAPED_UNICODE);
    }
}
