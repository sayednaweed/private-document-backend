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
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        //
    })->create();
