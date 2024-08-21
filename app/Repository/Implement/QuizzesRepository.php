<?php

namespace App\Repository\Implement;

use App\DTOs\Quizz\CreateQuizzDTO;
use App\Models\Quizze;
use App\Pipeline\Global\UserIdFilter;
use App\Pipeline\Quizzes\CategoryIdFilter;
use App\Repository\Interface\QuizzesRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;

readonly class QuizzesRepository implements QuizzesRepositoryInterface
{
    public function __construct(
        private Quizze $quizzes
    ) {
    }

    public function getQuery(array $columnSelects = [], array $filters = []): Builder
    {
        $query = $this->quizzes->query();
        if (count($columnSelects)) {
            $query->select($columnSelects);
        }

        return app(abstract: Pipeline::class)
            ->send($query)
            ->through([
                new CategoryIdFilter(filters: $filters),
                new UserIdFilter(filters: $filters),
            ])
            ->thenReturn();
    }

    public function listQuizzes(array $columnSelects = [], bool $isPaginate = false, array $filters = []): Collection | LengthAwarePaginator
    {
        if (!$isPaginate) {
            return $this->getQuery(filters: $filters)
                ->withCount(['questions', 'rooms'])
                ->with(relations: 'category:id,name')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return $this->getQuery(filters: $filters)
            ->withCount(['questions', 'rooms'])
            ->with(relations: 'category:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(perPage: config(key: 'app.quizzes.limit_pagination'));
    }

    public function createQuiz(CreateQuizzDTO $quizDTO): Quizze
    {
        $quiz = new Quizze();
        $quiz->title = $quizDTO->getTitle();
        $quiz->category_id = $quizDTO->getCategoryId();
        $quiz->user_id = $quizDTO->getUserId();
        $quiz->save();

        return $quiz;
    }
}
