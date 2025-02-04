<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SystemMiddleware;
use App\Http\Middleware\ViewProfileMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'is_admin' => AdminMiddleware::class,
            'is_system' => SystemMiddleware::class,
            'view_profile' => ViewProfileMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
//        $exceptions->render(function (AuthenticationException $e, Request $request) {
//            if ($request->is('api/*')) {
//                return response()->json([
//                    'message' => $e->getMessage(),
//                ], 401);
//            }
//        });
        $exceptions->respond(function (Response $response) {
//            if ($response->getStatusCode() === Response::HTTP_INTERNAL_SERVER_ERROR) {
//                return view("errors.internal-server");
//            }

            return $response;
        });
    })->create();
