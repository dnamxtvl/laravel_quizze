<?php

namespace App\Services\Implement;

use App\Repository\Interface\CategoryRepositoryInterface;
use App\Services\Interface\CategoryServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

readonly class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}


    public function getCategoryWithCountQuiz(Carbon $startTime, Carbon $endTime): Collection
    {
        return $this->categoryRepository->getCategoryWithCountQuiz(startTime: $startTime, endTime: $endTime);
    }

    public function getByIds(array $ids, Carbon $startTime, Carbon $endTime): Collection
    {
        return $this->categoryRepository->getByIds(ids: $ids);
    }
}
