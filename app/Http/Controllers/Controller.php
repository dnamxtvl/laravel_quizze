<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class Controller
{
    protected function respondWithJson(array $content, int $status = 200, array $headers = [], int $options = 0): JsonResponse
    {
        $response = [
            'data' => $content,
        ];

        return response()->json($response, $status, $headers, $options);
    }

    protected function respondWithJsonError(
        Throwable $e,
        array $headers = [],
        int $options = 0
    ): JsonResponse {
        $code = $e->getCode() ?? Response::HTTP_BAD_REQUEST;
        $message = $e->getMessage() ?? 'An error occurred!';
        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

        $content = [
            'message' => $message,
            'errors' => [
                'code' => $code,
            ],
        ];

        return response()->json($content, $status, $headers, $options);
    }
}
