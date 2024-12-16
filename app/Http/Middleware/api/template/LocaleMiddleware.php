<?php

namespace App\Http\Middleware\api\template;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the cookie locale is set
        $userLocale = $request->header("X-LOCALE");
        if (!$userLocale) {
            // Retrieve the default locale from the config
            $defaultLocale = config('app.locale');
            // Set the application locale to the session locale
            App::setLocale($defaultLocale);
        } else {
            // Check if the current locale is different from the session locale
            if (App::getLocale() !== $userLocale) {
                // Set the locale only if it differs
                App::setLocale($userLocale);
            }
        }
        return $next($request);
    }
}
