<?php

use App\Http\Middleware\api\template\AccessUserCheckMiddleware;
use App\Http\Middleware\api\template\AllowAdminOrSuperMiddleware;
use App\Http\Middleware\api\template\HasAddPermissionMiddleware;
use App\Http\Middleware\api\template\HasDeletePermissionMiddleware;
use App\Http\Middleware\api\template\HasEditPermissionMiddleware;
use App\Http\Middleware\api\template\HasGrantPermissionMiddleware;
use App\Http\Middleware\api\template\HasViewPermissionMiddleware;
use App\Http\Middleware\api\template\LocaleMiddleware;
use App\Http\Middleware\api\template\ValidateApiKey;
use App\Http\Middleware\web\EnsureUserIsMaster;
use App\Jobs\LogErrorJob;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(LocaleMiddleware::class)
            ->alias([
                'hasViewPermission' => HasViewPermissionMiddleware::class,
                'hasDeletePermission' => HasDeletePermissionMiddleware::class,
                'hasEditPermission' => HasEditPermissionMiddleware::class,
                'hasAddPermission' => HasAddPermissionMiddleware::class,
                'hasGrantPermission' => HasGrantPermissionMiddleware::class,
                'allowAdminOrSuper'  => AllowAdminOrSuperMiddleware::class,
                'isSuper'  => EnsureUserIsMaster::class,
                'accessUserCheck'  => AccessUserCheckMiddleware::class,
                'api.key' => ValidateApiKey::class,
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle all other exceptions globally


        $exceptions->renderable(function (Throwable $err) {
            if ($err instanceof \Illuminate\Validation\ValidationException) {
                // Skip processing for validation exceptions
                return null; // Let Laravel handle it as usual (validation errors are automatically handled)
            }
            $logData = [
                'error_code' => $err->getCode(),
                'trace' => $err->getTraceAsString(),
                'exception_type' => get_class($err),
                'error_message' => $err->getMessage(),
                'user_id' => request()->user() ? request()->user()->id : "N/K", // If you have an authenticated user, you can add the user ID
                'username' => request()->user() ? request()->user()->username : "N/K", // If you have an authenticated user, you can add the user ID
                'ip_address' => request()->ip(),
                'method' => request()->method(),
                'uri' => request()->fullUrl(),
            ];
            // Dispatch the logging job asynchronously
            LogErrorJob::dispatch($logData);
            Log::info('Global Exception =>' . $err->getMessage());
            return response()->json([
                'message' => __('app_translation.server_error')
            ], 500, [], JSON_UNESCAPED_UNICODE);
        });
    })->create();
