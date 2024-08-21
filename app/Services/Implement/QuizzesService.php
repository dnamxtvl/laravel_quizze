<?php

namespace App\Services\Implement;

use App\DTOs\Quizz\CreateQuizzDTO;
use App\Repository\Interface\QuestionRepositoryInterface;
use App\Repository\Interface\QuizzesRepositoryInterface;
use App\Services\Interface\QuizzesServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Throwable;

readonly class QuizzesService implements QuizzesServiceInterface
{
    public function __construct(
        private QuizzesRepositoryInterface $quizzesRepository,
        private QuestionRepositoryInterface $questionRepository,
    ) {
    }
    public function listQuizzes(): Collection | LengthAwarePaginator
    {
        return $this->quizzesRepository->listQuizzes(isPaginate: true, filters: ['user_id' => Auth::id()]);
    }

    /**
     * @throws InternalErrorException
     */
    public function createQuiz(CreateQuizzDTO $quizDTO, Array $questionDTO): void
    {
        DB::beginTransaction();
        try {
            $quiz = $this->quizzesRepository->createQuiz(quizDTO: $quizDTO);
            $this->questionRepository->insertQuestions(questions: $questionDTO, quizId: $quiz->id);
            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            throw new InternalErrorException(message: $th->getMessage());
        }
    }
}
