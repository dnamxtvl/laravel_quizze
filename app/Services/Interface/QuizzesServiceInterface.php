<?php

namespace App\Services\Interface;

use App\DTOs\Quizz\CreateQuizzDTO;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface QuizzesServiceInterface
{
    public function listQuizzes(): Collection | LengthAwarePaginator;

    public function createQuiz(CreateQuizzDTO $quizDTO, Array $questionDTO): void;
}
