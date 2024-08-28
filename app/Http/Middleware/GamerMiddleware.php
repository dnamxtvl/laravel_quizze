<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GamerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $content = [
                'message' => 'Bạn không có quyền truy cập trang này!',
                'errors' => [
                    'code' => 0,
                ],
            ];

            return response()->json($content, Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
