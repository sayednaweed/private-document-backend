<?php

namespace App\Http\Middleware\api\template;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');
        $serverIp = $request->header('X-SERVER-ADDR'); // This gets the IP address of the server the request is sent to



        // Check if the API key is provided
        if (!$apiKey) {
            return response()->json(['error' => 'API key required'], 401);
        }

        // Retrieve the API key record based on the IP address
        $row = ApiKey::select('hashed_key', 'is_active')->where('ip_address', $serverIp)->first();

        // Check if the row exists
        if (!$row) {
            return response()->json(['error' => 'Invalid or inactive API key'], 403);
        }

        // Validate the API key
        if (Hash::check($apiKey, $row->hashed_key) && $row->is_active) {

            return $next($request);
        } else {
            return response()->json(['error' => 'Invalid or inactive API key'], 403);
        }
    }
}
