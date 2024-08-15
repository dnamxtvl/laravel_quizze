<?php

namespace App\Http\Controllers;

use App\Services\Interface\QuizzesServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class QuizzesController extends Controller
{
    public function __construct(
        private readonly QuizzesServiceInterface $quizzesService,
    ) {}

    public function listQuizzesPagination(): JsonResponse
    {
        try {
            $listQuizzes = $this->quizzesService->listQuizzes();
            return $this->respondWithJson(content: $listQuizzes->toArray());
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }
}
