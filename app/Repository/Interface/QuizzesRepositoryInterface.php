<?php

namespace App\Repository\Interface;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface QuizzesRepositoryInterface
{
    public function listQuizzes(array $columnSelects = [], bool $isPaginate = false, array $filters = []): Collection | LengthAwarePaginator;
}
