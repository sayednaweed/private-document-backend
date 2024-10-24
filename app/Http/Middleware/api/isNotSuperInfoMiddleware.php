<?php

namespace App\Http\Middleware\api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class isNotSuperInfoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $paramId = $request->route('id');
        if ($request->id === "1" || $paramId === "1") {
            return response()->json([
                'message' => __('app_translation.unauthorized'),
            ], 403, [], JSON_UNESCAPED_UNICODE);
        }
        return $next($request);
    }
}
