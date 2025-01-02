<?php

namespace App\Repository\Interface;

use App\DTOs\Quizz\CreateQuizDTO;
use App\DTOs\Quizz\SearchQuizDTO;
use App\Enums\Quiz\TypeQuizEnum;
use App\Models\Quizze;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface QuizzesRepositoryInterface
{
    public function listQuizzes(TypeQuizEnum $type, array $columnSelects = [], bool $isPaginate = false, array $filters = []): Collection|LengthAwarePaginator;

    public function createQuiz(CreateQuizDTO $quizDTO): Quizze;

    public function findById(string $quizId): ?Quizze;

    public function searchQuiz(SearchQuizDTO $searchQuizDTO): LengthAwarePaginator;

    public function deleteQuiz(Quizze $quiz): void;

    public function getAll(): Collection;

    public function getMaxCode(bool $createdBySys): string;
}
