<?php

namespace App\Services\Implement;

use App\DTOs\Quizz\CreateQuizzDTO;
use App\Enums\Exception\ExceptionCodeEnum;
use App\Exceptions\Quiz\RoomIsRunningException;
use App\Models\Quizze;
use App\Repository\Interface\QuestionRepositoryInterface;
use App\Repository\Interface\QuizzesRepositoryInterface;
use App\Repository\Interface\RoomRepositoryInterface;
use App\Services\Interface\QuizzesServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

readonly class QuizzesService implements QuizzesServiceInterface
{
    public function __construct(
        private QuizzesRepositoryInterface $quizzesRepository,
        private QuestionRepositoryInterface $questionRepository,
        private RoomRepositoryInterface $roomRepository,
    ) {}

    public function listQuizzes(): Collection|LengthAwarePaginator
    {
        $quizzes = Quizze::query()->with('questions')->withTrashed()->get();
//        foreach ($quizzes as $quiz) {
//            foreach ($quiz->questions as $index => $question) {
//                $question->index_question = $index + 1;
//                $question->save();
//            }
//        }
        return $this->quizzesRepository->listQuizzes(isPaginate: true, filters: ['user_id' => Auth::id()]);
    }

    /**
     * @throws InternalErrorException
     */
    public function createQuiz(CreateQuizzDTO $quizDTO, array $questionDTO): void
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

    /**
     * @throws InternalErrorException
     */
    public function deleteQuiz(string $quizId): void
    {
        DB::beginTransaction();
        $quiz = $this->quizzesRepository->findById(quizId: $quizId);
        if (is_null($quiz)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy bộ câu hỏi!');
        }

        $listRoomRunning = $this->roomRepository->getListRoomRunning(quizId: $quizId);
        if ($listRoomRunning->count() > 0) {
            $listRoomCode = $listRoomRunning->pluck('code', 'id')->toArray();
            $listCodeValue = implode(',', array_unique($listRoomCode));
            throw new RoomIsRunningException(
                message: 'Các room ' . $listCodeValue . ' chưa kết thúc, bạn không thể xóa quizz!',
                code: ExceptionCodeEnum::ROOM_IS_NOT_FINISHED->value
            );
        }

        try {
            $quiz->delete();
            $this->questionRepository->deleteQuestionByQuiz(quizId: $quizId);
            Db::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            throw new InternalErrorException(message: $th->getMessage());
        }
    }

    public function listQuestionOfQuiz(string $quizId): Collection
    {
        return $this->questionRepository->listQuestionOfQuiz(quizId: $quizId);
    }
}
