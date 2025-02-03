<?php

namespace App\Repository\Implement;

use App\Models\Category;
use App\Models\Quizze;
use App\Repository\Interface\CategoryRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

readonly class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private Category $category
    ) {}

    public function listCategory(): Collection
    {
        return $this->category->all();
    }

    public function getCategoryWithCountQuiz(Carbon $startTime, Carbon $endTime): Collection
    {
        return $this->category->query()
            ->withCount(['quizzes' => function ($query) use ($startTime, $endTime) {
                $query->whereBetween('created_at', [$endTime, $startTime]);
            }])
            ->orderBy('quizzes_count', 'desc')
            ->limit(10)
            ->get();
    }

    public function getByIds(array $ids, Carbon $startTime, Carbon $endTime): Collection
    {
        return $this->category->query()
            ->whereIn('id', $ids)
            ->withCount(['quiz' => function ($query) use ($startTime, $endTime) {
                $query->whereBetween('created_at', [$startTime, $endTime]);
            }])
            ->get();
    }
}
