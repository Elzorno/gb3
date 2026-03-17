<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\BlockWritesWhenFrozen;
use App\Http\Middleware\KidAuth;
use App\Http\Middleware\SecureHeaders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware - always runs
        $middleware->append(BlockWritesWhenFrozen::class);
        $middleware->append(SecureHeaders::class);
        
        // Named middleware aliases
        $middleware->alias([
            'admin.auth' => AdminAuth::class,
            'kid.auth' => KidAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
