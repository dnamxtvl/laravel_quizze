<?php

namespace App\Repository\Implement;

use App\DTOs\Quizz\CreateQuizDTO;
use App\DTOs\Quizz\SearchQuizDTO;
use App\Enums\Quiz\TypeQuizEnum;
use App\Models\Quizze;
use App\Pipeline\Global\CodeFilter;
use App\Pipeline\Global\CreatedAtBetweenFilter;
use App\Pipeline\Global\CreatedBySysFilter;
use App\Pipeline\Global\UserIdFilter;
use App\Pipeline\Global\UserIdsFiler;
use App\Pipeline\Quizzes\CategoryIdFilter;
use App\Repository\Interface\QuizzesRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;

readonly class QuizzesRepository implements QuizzesRepositoryInterface
{

    const DEFAULT_CODE = 10000001;
    const PREPARE_SYS_CODE = 'SY';
    const PREPARE_USER_CODE = 'US';

    public function __construct(
        private Quizze $quizzes,
    ) {}

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
                new UserIdsFiler(filters: $filters),
                new CodeFilter(filters: $filters),
                new CreatedAtBetweenFilter(filters: $filters),
                new CreatedBySysFilter(filters: $filters),
            ])
            ->thenReturn();
    }

    public function listQuizzes(TypeQuizEnum $type, array $columnSelects = [], bool $isPaginate = false, array $filters = []): Collection|LengthAwarePaginator
    {
        $builderQuiz = $this->getQuery(filters: $filters)
            ->select([
                'quizzes.id',
                'quizzes.title',
                'quizzes.category_id',
                'quizzes.user_id',
                'quizzes.created_at',
                'quizzes.updated_at',
            ])
            ->withCount(['questions' => fn (Builder $query) => $query->where('is_old_question', false), 'rooms'])
            ->with(relations: ['category:id,name', 'user:id,name']);

        $builderSharedWithMe = $this->quizzes->query()
            ->select([
                'quizzes.id',
                'quizzes.title',
                'quizzes.category_id',
                'quizzes.user_id',
                'user_share_quizzes.accepted_at as created_at',
                'quizzes.updated_at',
            ])
            ->join('user_share_quizzes', 'quizzes.id', '=', 'user_share_quizzes.quizze_id')
            ->where('user_share_quizzes.receiver_id', $filters['user_id'])
            ->where('user_share_quizzes.is_accept', true)
            ->withCount(['questions' => fn (Builder $query) => $query->where('is_old_question', false), 'rooms'])
            ->with(relations: ['category:id,name', 'user:id,name']);

        if ($type == TypeQuizEnum::SHARE_WITH_ME) {
            return !$isPaginate ? $builderSharedWithMe->orderBy('created_at', 'desc')->get() :
                $builderSharedWithMe->orderBy('created_at', 'desc')
                    ->paginate(perPage: config(key: 'app.quizzes.limit_pagination'));
        }

        if ($type == TypeQuizEnum::CREATED_BY_ME) {
            return !$isPaginate ? $builderQuiz->orderBy('created_at', 'desc')->get() :
                $builderQuiz->orderBy('created_at', 'desc')
                    ->paginate(perPage: config(key: 'app.quizzes.limit_pagination'));
        }

        return !$isPaginate ? $builderQuiz->union($builderSharedWithMe)->orderBy('created_at', 'desc')->get() :
            $builderQuiz->union($builderSharedWithMe)
                ->orderBy('created_at', 'desc')
                ->paginate(perPage: config(key: 'app.quizzes.limit_pagination'));
    }

    public function createQuiz(CreateQuizDTO $quizDTO): Quizze
    {
        $quiz = new Quizze;
        $quiz->title = $quizDTO->getTitle();
        $quiz->category_id = $quizDTO->getCategoryId();
        $quiz->code = $quizDTO->getCode();
        $quiz->user_id = $quizDTO->getUserId();
        $quiz->created_by_sys = $quizDTO->getCreatedBySys();
        $quiz->save();

        return $quiz;
    }

    public function findById(string $quizId): ?Quizze
    {
        return $this->quizzes->query()->find(id: $quizId);
    }

    public function searchQuiz(SearchQuizDTO $searchQuizDTO): LengthAwarePaginator
    {
        return $this->getQuery(filters: $searchQuizDTO->toArray())
            ->with(['user', 'category:id,name'])
            ->whereHas('user')
            ->withCount(['questions' => fn (Builder $query) => $query->where('is_old_question', false), 'rooms'])
            ->orderBy(column: 'created_at', direction: 'desc')
            ->orderBy('id', 'desc')
            ->paginate(perPage: config(key: 'app.quizzes.limit_pagination_search'));
    }

    public function deleteQuiz(Quizze $quiz): void
    {
        $quiz->deleted_by = Auth::id();
        $quiz->deleted_at = now();
        $quiz->save();
    }

    public function getAll(): Collection
    {
        return $this->quizzes->query()->orderBy('id')->get();
    }

    public function getMaxCode(bool $createdBySys): string
    {
        $lastQuiz = $this->quizzes->query()
            ->where('created_by_sys', $createdBySys)
            ->orderBy('code', 'desc')
            ->lockForUpdate()
            ->first();

        $prepareCode = !$createdBySys ? self::PREPARE_USER_CODE : self::PREPARE_SYS_CODE;
        $defaultCode = (string)self::DEFAULT_CODE;
        $lastCode = $prepareCode . $defaultCode;
        if ($lastQuiz) {
            $newCode = (int)preg_replace('/\D/', '', $lastQuiz->code) + 1;
            $lastCode = (string)$newCode;
            $lastCode = $prepareCode . $lastCode;
        }

        return $lastCode;
    }
}
