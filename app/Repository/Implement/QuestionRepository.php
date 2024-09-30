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
            ->where('is_old_question', false)
            ->orderBy('id')
            ->first();
    }

    public function insertQuestions(array $questions, string $quizId): array
    {
        $now = now();
        $questionsInsert = [];
        $answersInsert = [];
        foreach ($questions as $index => $question) {
            $questionId = Str::orderedUuid();
            /* @var CreateQuestionDTO $question */
            $questionsInsert[] = [
                'id' => $questionId,
                'quizze_id' => $quizId,
                'title' => $question->getTitle(),
                'index_question' => $index + 1,
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
        $this->question->query()->insert(values: $questionsInsert);
        $this->answer->query()->insert(values: $answersInsert);

        return collect($questionsInsert)->pluck('id')->toArray();
    }

    public function deleteQuestionByQuiz(string $quizId): void
    {
        $questionIds = $this->getQuery(filters: ['quizze_id' => $quizId])->pluck('id')->toArray();
        $this->question->query()->whereIn('id', $questionIds)->delete();
    }

    public function listQuestionOfQuiz(string $quizId): Collection
    {
        return $this->question->query()
            ->where('quizze_id', $quizId)
            ->where('is_old_question', false)
            ->with(['answers', 'quizze'])
            ->orderBy('index_question')
            ->get();
    }

    public function listQuestionByIds(array $questionIds): Collection
    {
        return $this->question->withTrashed()
            ->with(['answers' => fn ($q) => $q->withTrashed()])
            ->whereIn('id', $questionIds)
            ->get();
    }

    public function createQuestion(CreateQuestionDTO $questionDTO, ?int $indexQuestionOverride = null): Question
    {
        $now = now();
        $question = new Question;
        $question->quizze_id = $questionDTO->getQuizId();
        $question->title = $questionDTO->getTitle();
        if ($indexQuestionOverride) {
            $question->index_question = $indexQuestionOverride;
        }
        $question->save();

        $answersInsert = [];
        foreach ($questionDTO->getAnswers() as $answer) {
            /* @var CreateAnswerDTO $answer */
            $answersInsert[] = [
                'question_id' => $question->id,
                'answer' => $answer->getAnswer(),
                'is_correct' => $answer->getIsCorrect(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->answer->query()->insert(values: $answersInsert);

        return $question;
    }

    public function deleteQuestion(Question $question): void
    {
        $question->answers()->delete();
        $question->delete();
    }

    public function findById(string $questionId): ?Question
    {
        return $this->question->query()->find(id: $questionId);
    }

    public function setIsOldQuestion(Question $question, bool $isOldQuestion): void
    {
        $question->is_old_question = $isOldQuestion;
        $question->save();
    }
}
