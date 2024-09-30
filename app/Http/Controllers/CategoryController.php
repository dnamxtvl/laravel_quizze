<?php

namespace App\Http\Controllers;

use App\Repository\Interface\CategoryRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function listCategory(): JsonResponse
    {
        try {
            $categories = $this->categoryRepository->listCategory();

            return $this->respondWithJson(content: $categories->toArray());
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }
}
