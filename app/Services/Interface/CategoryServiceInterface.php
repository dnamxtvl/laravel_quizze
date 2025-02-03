<?php

namespace App\Services\Interface;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface CategoryServiceInterface
{
    public function getCategoryWithCountQuiz(Carbon $startTime, Carbon $endTime): Collection;

    public function getByIds(array $ids, Carbon $startTime, Carbon $endTime): Collection;
}
