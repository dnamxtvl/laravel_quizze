<?php

namespace App\Services\Implement;

use App\Repository\Interface\QuizzesRepositoryInterface;
use App\Services\Interface\QuizzesServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

readonly class QuizzesService implements QuizzesServiceInterface
{
    public function __construct(
        private QuizzesRepositoryInterface $quizzesRepository
    ) {
    }
    public function listQuizzes(): Collection | LengthAwarePaginator
    {
        return $this->quizzesRepository->listQuizzes(isPaginate: true);
    }
}
