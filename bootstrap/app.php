<?php

use App\Http\Middleware\api\EnsureUserIsAdminOrSuper;
use App\Http\Middleware\api\HasAddPermissionMiddleware;
use App\Http\Middleware\api\HasDeletePermissionMiddleware;
use App\Http\Middleware\api\HasEditPermissionMiddleware;
use App\Http\Middleware\api\HasViewPermissionMiddleware;
use App\Http\Middleware\api\LocaleMiddleware;
use App\Http\Middleware\api\ValidateApiKey;
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
                'isMaster'  => EnsureUserIsMaster::class,
                'isAdminOrSuper'  => EnsureUserIsAdminOrSuper::class,
                'api.key' => ValidateApiKey::class,
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
