<?php

namespace App\Repository\Interface;

use App\DTOs\Quizz\CreateQuizzDTO;
use App\Models\Quizze;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface QuizzesRepositoryInterface
{
    public function listQuizzes(array $columnSelects = [], bool $isPaginate = false, array $filters = []): Collection | LengthAwarePaginator;

    public function createQuiz(CreateQuizzDTO $quizDTO): Quizze;
}
