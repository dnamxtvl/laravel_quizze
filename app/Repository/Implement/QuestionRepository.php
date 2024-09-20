<?php

namespace App\Repository\Implement;

use App\DTOs\Answer\CreateAnswerDTO;
use App\DTOs\Question\CreateQuestionDTO;
use App\Models\Answer;
use App\Models\Question;
use App\Pipeline\Global\QuizzIdFilter;
use App\Repository\Interface\QuestionRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;

readonly class QuestionRepository implements QuestionRepositoryInterface
{
    public function __construct(
        private Question $question,
        private Answer $answer,
    ) {}

    public function getQuery(array $columnSelects = [], array $filters = []): Builder
    {
        $query = $this->question->query();
        if (count($columnSelects)) {
            $query->select($columnSelects);
        }

        return app(abstract: Pipeline::class)
            ->send($query)
            ->through([
                new QuizzIdFilter(filters: $filters),
            ])
            ->thenReturn();
    }

    public function listQuestion(array $columnSelects = [], array $filters = []): Collection
    {
        return $this->getQuery(filters: $filters)->with(relations: 'answers')->get();
    }

    public function findNextQuestion(string $quzId, string $questionId): ?Question
    {
        return $this->question->query()
            ->where('quizze_id', $quzId)
            ->where('id', '>', $questionId)
            ->orderBy('id')
            ->first();
    }

    public function insertQuestions(array $questions, string $quizId): array
    {
        $now = now();
        $questionsInsert = [];
        $answersInsert = [];
        foreach ($questions as $question) {
            $questionId = Str::orderedUuid();
            /* @var CreateQuestionDTO $question */
            $questionsInsert[] = [
                'id' => $questionId,
                'quizze_id' => $quizId,
                'title' => $question->getTitle(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            foreach ($question->getAnswers() as $answer) {
                /* @var CreateAnswerDTO $answer */
                $answersInsert[] = [
                    'question_id' => $questionId,
                    'answer' => $answer->getAnswer(),
                    'is_correct' => $answer->getIsCorrect(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        $this->question->query()->insert($questionsInsert);
        $this->answer->query()->insert($answersInsert);

        return collect($questionsInsert)->pluck('id')->toArray();
    }

    public function deleteQuestion(string $quizId): void
    {
        $questionIds = $this->getQuery(filters: ['quizze_id' => $quizId])->pluck('id')->toArray();
        $this->answer->query()->whereIn('question_id', $questionIds)->delete();
        $this->question->query()->whereIn('id', $questionIds)->delete();
    }
}
