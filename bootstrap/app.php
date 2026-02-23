<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'model.access' => \App\Http\Middleware\EnsureUserCanAccessModel::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // When session/CSRF expires (419), redirect to login with a fresh form instead of showing "Page Expired"
        $exceptions->renderable(function (Throwable $e, $request) {
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                return redirect()->route('login')
                    ->with('error', 'نشست منقضی شده است. لطفاً دوباره وارد شوید.')
                    ->withInput($request->only('email'));
            }
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : null;
            if ($status === 419 && $request->expectsJson() === false) {
                return redirect()->route('login')
                    ->with('error', 'نشست منقضی شده است. لطفاً دوباره وارد شوید.')
                    ->withInput($request->only('email'));
            }
            return null;
        });
    })->create();
