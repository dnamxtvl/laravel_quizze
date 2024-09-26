<?php

namespace App\Repository\Interface;

use App\DTOs\Question\CreateQuestionDTO;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface QuestionRepositoryInterface
{
    public function listQuestion(array $columnSelects = [], array $filters = []): Collection;

    public function findNextQuestion(string $quzId, string $questionId): ?Question;

    public function insertQuestions(array $questions, string $quizId): array;

    public function deleteQuestionByQuiz(string $quizId): void;

    public function listQuestionOfQuiz(string $quizId): Collection;

    public function listQuestionByIds(array $questionIds): Collection;

    public function createQuestion(CreateQuestionDTO $questionDTO, ?int $indexQuestionOverride = null): Question;

    public function deleteQuestion(Question $question): void;

    public function findById(string $questionId): ?Question;

    public function setIsOldQuestion(Question $question, bool $isOldQuestion): void;
}
