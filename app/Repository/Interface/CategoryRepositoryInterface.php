<?php

namespace App\Repository\Interface;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function listCategory(): Collection;

    public function getCategoryWithCountQuiz(Carbon $startTime, Carbon $endTime): Collection;

    public function getByIds(array $ids, Carbon $startTime, Carbon $endTime): Collection;
}
