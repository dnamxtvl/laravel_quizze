<?php

namespace App\Http\Middleware;

use App\Enums\User\UserRoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->type != UserRoleEnum::ADMIN->value) {
            $content = [
                'message' => 'Bạn không có quyền truy cập trang này!',
                'errors' => [
                    'code' => 0,
                ],
            ];

            return response()->json($content, Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
