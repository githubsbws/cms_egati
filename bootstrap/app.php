<?php

use App\Http\Middleware\CheckIdleTimeout;
// use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\EnsureMenuPermission;
use App\Http\Middleware\EnsureSingleSession;
use App\Http\Middleware\SetAdminLocale;
use App\Http\Middleware\SetWebLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetAdminLocale::class,
            CheckIdleTimeout::class,
            EnsureSingleSession::class,
        ]);

        $middleware->alias([
            'checkIdleTimeout' => CheckIdleTimeout::class,
            // 'ensureIsAdmin' => EnsureIsAdmin::class,
            'menu.permission' => EnsureMenuPermission::class,
            'web.locale' => SetWebLocale::class,
        ]);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/admin');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
