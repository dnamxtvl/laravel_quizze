<?php

namespace App\Services\Interface;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface QuizzesServiceInterface
{
    public function listQuizzes(): Collection | LengthAwarePaginator;
}
