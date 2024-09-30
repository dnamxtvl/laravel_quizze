<?php

namespace App\Services\Implement;

use App\DTOs\Notification\CreateNotifyDTO;
use App\DTOs\Quizz\CreateQuizzDTO;
use App\DTOs\UserShareQuiz\CreateUserShareQuizDTO;
use App\Enums\Exception\ExceptionCodeEnum;
use App\Enums\Notification\TypeNotifyEnum;
use App\Enums\Quiz\TypeQuizEnum;
use App\Enums\User\UserRoleEnum;
use App\Events\ShareQuizEvent;
use App\Exceptions\Quiz\RoomIsRunningException;
use App\Exceptions\Quiz\UnAuthorizeRejectQuizException;
use App\Exceptions\Quiz\UnAuthorizeShareQuizException;
use App\Models\User;
use App\Models\UserShareQuiz;
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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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

    public function listQuizzes(TypeQuizEnum $type): Collection|LengthAwarePaginator
    {
        return $this->quizzesRepository->listQuizzes(
            type: $type,
            isPaginate: true,
            filters: ['user_id' => Auth::id()]
        );
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
        $quiz = $this->quizzesRepository->findById(quizId: $quizId);
        if (is_null($quiz)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy bộ cảu hỏi!');
        }

        if ($quiz->user_id != Auth::id()) {
            $isUserShared =  $this->userShareQuestionRepository->getQuery(filters: ['quizze_id' => $quizId, 'receiver_id' => Auth::id()])
                ->where('is_accept', true)->exists();
            if (!$isUserShared) {
                throw new UnAuthorizeShareQuizException(
                    message: 'Bạn không có quyền xem bộ câu hỏi này!',
                    code: ExceptionCodeEnum::NOT_PERMISSION_VIEW_QUIZ->value
                );
            }
        }

        return $this->questionRepository->listQuestionOfQuiz(quizId: $quizId);
    }

    /**
     * @throws InternalErrorException
     */
    public function shareQuiz(string $quizId, string $email): void
    {
        $user = $this->userRepository->findByEmail(email: $email);
        if (is_null($user) || $user->role != UserRoleEnum::ADMIN->value) {
            throw new NotFoundHttpException(message: 'Không tìm thấy người dùng!');
        }

        $quiz = $this->quizzesRepository->findById(quizId: $quizId);
        if (is_null($quiz)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy bộ cảu hỏi!');
        }

        $authUser = Auth::user();
        /* @var User $authUser */
        if ($authUser->email == $email) {
            throw new BadRequestHttpException(message: 'Không thể chia sẻ cho chính mình!');
        }

        $authId = $authUser->id;

        if ($quiz->user_id != $authId) {
            $authReceiver = $this->userShareQuestionRepository->findAuthReceiver(filters: [
                'receiver_id' => $authId,
                'quizze_id' => $quizId,
            ]);
            if (is_null($authReceiver)) {
                throw new UnAuthorizeShareQuizException(code: ExceptionCodeEnum::UNAUTHORIZED_TO_SHARE_QUIZ->value);
            }
        }

        $userShareQuiz = new CreateUserShareQuizDTO(
            userShareId: $authId,
            quizId: $quizId,
            receiverId: $user->id,
            token: hash('sha512', ($authId . $user->id . $quizId . Str::uuid())),
        );

        $userShared = $this->userShareQuestionRepository->getQuery(
            filters: ['receiver_id' => $user->id, 'quizze_id' => $quizId]
        )->first();

        if (!is_null($userShared) && $userShared->is_accept) {
            throw new UnAuthorizeShareQuizException(message: $email . ' đã được chia sẽ bộ cảu hỏi từ truớc đó!');
        }

        $linkPath = config('app.front_end_url') . config('app.quiz.path_link_verify_share') . '/';
        $notify = new CreateNotifyDTO(
            userId: $user->id,
            title: "Bộ câu hỏi mới!",
            content: $authUser->name . ' đã chia sẽ bộ cảu hỏi ' . $quiz->title . ' với bạn!',
            type: TypeNotifyEnum::SHARE_QUIZ,
            link: !is_null($userShared) ? $linkPath . $userShared->token
                : $linkPath . $userShareQuiz->getToken(),
        );

        DB::beginTransaction();
        try {
            if (is_null($userShared)) {
                $this->userShareQuestionRepository->createUserShareQuiz(userShareQuizDTO: $userShareQuiz);
            }
            $newNotify = $this->notificationRepository->createNotify(notifyDTO: $notify);
            broadcast(new ShareQuizEvent(
                userId: $user->id,
                link: $notify->getLink(),
                title: $newNotify->title,
                content: $newNotify->content,
                createdAt: $newNotify->created_at,
                notifyId: $newNotify->id,
            ))->toOthers();
            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error(message: $th->getMessage());
            throw new InternalErrorException(message: 'Đã xảy ra lỗi!');
        }
    }

    /**
     * @throws InternalErrorException
     */
    public function acceptShareQuiz(string $token, ?string $notifyId = null): void
    {
        $userShareQuiz = $this->userShareQuestionRepository->findByToken(token: $token);
        if (is_null($userShareQuiz)) {
            throw new NotFoundHttpException(message: 'Token không hợp lệ hoặc đã hết hạn!');
        }

        if ($userShareQuiz->is_accept) {
            throw new BadRequestHttpException(
                message: 'Bạn đã nhận chia sẻ bộ câu hỏi này trước đó rồi!',
                code: ExceptionCodeEnum::SHARED_QUIZ->value,
            );
        }

        $notify = null;
        if (!is_null($notifyId)) {
            $notify = $this->notificationRepository->findById(notifyId: $notifyId);
        }

        DB::beginTransaction();
        try {
            $this->userShareQuestionRepository->acceptShareQuiz(userShareQuiz: $userShareQuiz);
            if ($notify) $this->notificationRepository->deleteNotify(notification: $notify);
            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error(message: $th->getMessage());
            throw new InternalErrorException(message: 'Đã xảy ra lỗi!');
        }
    }

    public function detailShareQuiz(string $token, ?string $notifyId = null): UserShareQuiz
    {
        if (!is_null($notifyId)) {
            $notify = $this->notificationRepository->findById(notifyId: $notifyId);
            if ($notify && !$notify->is_read) $this->notificationRepository->readNotify(notification: $notify);
        }

        $userShare = $this->userShareQuestionRepository->findByToken(token: $token);
        if (is_null($userShare) || $userShare->receiver_id != Auth::id()) {
            throw new NotFoundHttpException(
                message: 'Token không hợp lệ hoặc yêu cầu chia sẻ trước đó đã bị từ chối!',
                code: ExceptionCodeEnum::REJECTED_QUIZ->value,
            );
        }

        return $userShare;
    }

    /**
     * @throws InternalErrorException
     */
    public function rejectShareQuiz(string $token, ?string $notifyId = null): void
    {
        $userShared = $this->userShareQuestionRepository->findByToken(token: $token);
        if (is_null($userShared) || $userShared->receiver_id != Auth::id()) {
            throw new NotFoundHttpException(message: 'Token không hợp lệ hoặc đã hết hạn!');
        }

        $notify = null;
        if (!is_null($notifyId)) {
            $notify = $this->notificationRepository->findById(notifyId: $notifyId);
        }
        DB::beginTransaction();
        try {
            if ($userShared->is_accept) {
                throw new UnAuthorizeRejectQuizException(code: ExceptionCodeEnum::RECEIVED_QUIZ->value);
            }
            $this->userShareQuestionRepository->rejectShareQuiz(userShareQuiz: $userShared);
            if ($notify) $this->notificationRepository->deleteNotify(notification: $notify);

            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error(message: $th->getMessage());
            throw new InternalErrorException(message: 'Đã xảy ra lỗi!');
        }
    }
}
