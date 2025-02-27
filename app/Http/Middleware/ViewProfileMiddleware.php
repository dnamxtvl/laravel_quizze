<?php

namespace App\Http\Middleware;

use App\Enums\User\UserRoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ViewProfileMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->type == UserRoleEnum::SYSTEM->value) {
            return $next($request);
        }

        if ($request->user()->id != $request->userId) {
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
