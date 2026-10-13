<?php

// Some managed hosts deploy with Composer's authoritative class map but do not
// provide a Composer binary. Keep application classes discoverable after a Git
// deployment even when the host cannot run `composer dump-autoload`.
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $path = __DIR__.'/../app/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';

    if (is_file($path)) {
        require_once $path;
    }
}, prepend: true);

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
        $middleware->redirectGuestsTo(fn (Request $request): string => match (true) {
            $request->is('campus/*') => route('filament.campus.auth.login'),
            $request->is('franchisor/*') => route('filament.franchisor.auth.login'),
            default => route('filament.admin.auth.login'),
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
