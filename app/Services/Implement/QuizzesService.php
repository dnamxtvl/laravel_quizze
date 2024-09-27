<?php

namespace App\Services\Implement;

use App\DTOs\Notification\CreateNotifyDTO;
use App\DTOs\Quizz\CreateQuizzDTO;
use App\DTOs\UserShareQuiz\CreateUserShareQuizDTO;
use App\Enums\Exception\ExceptionCodeEnum;
use App\Enums\Notification\TypeNotifyEnum;
use App\Enums\User\UserRoleEnum;
use App\Events\ShareQuizEvent;
use App\Exceptions\Quiz\RoomIsRunningException;
use App\Exceptions\Quiz\UnAuthorzireShareQuizException;
use App\Models\User;
use App\Repository\Interface\NotificationRepositoryInterface;
use App\Repository\Interface\QuestionRepositoryInterface;
use App\Repository\Interface\QuizzesRepositoryInterface;
use App\Repository\Interface\RoomRepositoryInterface;
use App\Repository\Interface\UserRepositoryInterface;
use App\Repository\Interface\UserShareQuizRepositoryInterface;
use App\Services\Interface\QuizzesServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

readonly class QuizzesService implements QuizzesServiceInterface
{
    public function __construct(
        private QuizzesRepositoryInterface       $quizzesRepository,
        private QuestionRepositoryInterface      $questionRepository,
        private RoomRepositoryInterface          $roomRepository,
        private UserRepositoryInterface          $userRepository,
        private UserShareQuizRepositoryInterface $userShareQuestionRepository,
        private NotificationRepositoryInterface $notificationRepository,
    ) {}

    public function listQuizzes(): Collection|LengthAwarePaginator
    {
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
                message: 'Các room '.$listCodeValue.' chưa kết thúc, bạn không thể xóa quizz!',
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

    /**
     * @throws InternalErrorException
     */
    public function shareQuiz(string $quizId, string $email): void
    {
        $user = $this->userRepository->findByEmail(email: $email);
        if (is_null($user) || $user->role != UserRoleEnum::ADMIN) {
            throw new NotFoundHttpException(message: 'Không tìm thấy người dùng!');
        }

        $quiz = $this->quizzesRepository->findById(quizId: $quizId);
        if (is_null($quiz)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy bộ cảu hỏi!');
        }

        $authUser = Auth::user();
        /* @var User $authUser */
        $authId = $authUser->id;

        if ($quiz->user_id != $authId) {
            $authReceiver = $this->userShareQuestionRepository->findAuthReceiver(filters: [
                'receiver_id' => $authId,
                'quizze_id' => $quizId,
            ]);
            if (is_null($authReceiver)) {
                throw new UnAuthorzireShareQuizException(code: ExceptionCodeEnum::UNAUTHORIZED_TO_SHARE_QUIZ->value);
            }
        }

        $userShareQuiz = new CreateUserShareQuizDTO(
            userShareId: $authId,
            quizId: $quizId,
            receiverId: $user->id,
            token: hash('sha512', ($authId . $user->id . $quizId . Str::uuid())),
        );

        $notify = new CreateNotifyDTO(
            userId: $user->id,
            title: "Bộ câu hỏi mới!",
            content: $authUser->name . ' đã chia sẽ bộ cảu hỏi ' . $quiz->title . 'với bạn!',
            type: TypeNotifyEnum::SHARE_QUIZ
        );

        DB::beginTransaction();
        try {
            $this->userShareQuestionRepository->createUserShareQuiz(userShareQuizDTO: $userShareQuiz);
            $this->notificationRepository->createNotify(notifyDTO: $notify);
            broadcast(new ShareQuizEvent(userId: $user->id))->toOthers();
            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error(message: $th->getMessage());
            throw new InternalErrorException(message: 'Đã xảy ra lỗi!');
        }
    }
}
