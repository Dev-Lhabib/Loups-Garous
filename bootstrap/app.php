<?php

use App\Exceptions\GameActionException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            '/broadcasting/auth',
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);

        $middleware->trustProxies(
            at: '*',
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (GameActionException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 403);
            }

            session()->flash('error', $e->getMessage());
            return redirect()->route('home');
        });

        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('errors.room_not_found')], 404);
            }
        });

        $exceptions->renderable(function (HttpException $e, $request) {
            if ($e->getStatusCode() === 403 || $e->getStatusCode() === 401) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => $e->getMessage() ?: __('errors.access_denied'),
                    ], $e->getStatusCode());
                }
            }
        });
    })->create();
