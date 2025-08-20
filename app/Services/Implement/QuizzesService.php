<?php

namespace App\Services\Implement;

use App\DTOs\Notification\CreateNotifyDTO;
use App\DTOs\Quizz\CreateQuizDTO;
use App\DTOs\Quizz\SearchQuizDTO;
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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

readonly class QuizzesService implements QuizzesServiceInterface
{
    public function __construct(
        private QuizzesRepositoryInterface $quizzesRepository,
        private QuestionRepositoryInterface $questionRepository,
        private RoomRepositoryInterface $roomRepository,
        private UserRepositoryInterface $userRepository,
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
    public function createQuiz(CreateQuizDTO $quizDTO, array $questionDTO): void
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            /** @var User $user */
            $maxCode = $this->quizzesRepository->getMaxCode(createdBySys: $user->type == UserRoleEnum::SYSTEM->value);
            $quizDTO->setCode(code: $maxCode);
            $quiz = $this->quizzesRepository->createQuiz(quizDTO: $quizDTO);
            $this->questionRepository->insertQuestions(questions: $questionDTO, quizId: $quiz->id);
            Log::info(message: $user->name . ' đã tạo bộ câu hỏi ' . $quiz->title, context: ['quiz' => $quiz]);

            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error($th);
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

        $user = Auth::user();
        /** @var User $user */
        $authReceiver = null;

        if ($quiz->user_id != $user->id && $user->type != UserRoleEnum::SYSTEM->value) {
            $authReceiver = $this->userShareQuestionRepository->findAuthReceiver(filters: [
                'receiver_id' => Auth::id(),
                'quizze_id' => $quizId,
            ]);

            if (!$authReceiver) {
                throw new UnAuthorizeShareQuizException(
                    message: 'Bạn không có quyền xóa bộ câu hỏi này',
                    code: ExceptionCodeEnum::UNAUTHORIZED_TO_SHARE_QUIZ->value
                );
            }
        }

        if ($user->type == UserRoleEnum::ADMIN->value) {
            $listRoomRunning = $this->roomRepository->getListRoomRunning(quizId: $quizId);
            if ($listRoomRunning->count() > 0 && !$authReceiver) {
                $listRoomCode = $listRoomRunning->pluck('code', 'id')->toArray();
                $listCodeValue = implode(',', array_unique($listRoomCode));
                throw new RoomIsRunningException(
                    message: 'Các room '.$listCodeValue.' chưa kết thúc, bạn không thể xóa quizz!',
                    code: ExceptionCodeEnum::ROOM_IS_NOT_FINISHED->value
                );
            }
        }

        if ($authReceiver) {
            $this->userShareQuestionRepository->deleteShareQuiz(userShareQuiz: $authReceiver);
        }

        try {
            if ($quiz->user_id == Auth::id() || $user->type == UserRoleEnum::SYSTEM->value) {
                $this->quizzesRepository->deleteQuiz(quiz: $quiz);
                $this->questionRepository->deleteQuestionByQuiz(quizId: $quizId);
            }
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

        $user = Auth::user();
        /** @var User $user */
        if ($quiz->user_id != $user->id && $user->type != UserRoleEnum::SYSTEM->value) {
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
        if (is_null($user) || $user->type != UserRoleEnum::ADMIN->value) {
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

        if ($quiz->user_id != $authId && $authUser->type != UserRoleEnum::SYSTEM->value) {
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

        if ((!is_null($userShared) && $userShared->is_accept) || $user->id == $quiz->user_id) {
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

            $messaging = Firebase::messaging();
            $deviceToken = $user->fcm_token;
            if ($deviceToken) {
                $message = CloudMessage::withTarget('token', $deviceToken)
                    ->withNotification(Notification::create('🔥' . $newNotify->title, $newNotify->content))
                    ->withData([
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'link' => $notify->getLink()
                    ]);

                $messaging->send($message);
            }

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

    public function searchQuiz(SearchQuizDTO $searchQuizDTO): LengthAwarePaginator
    {
        return $this->quizzesRepository->searchQuiz(searchQuizDTO: $searchQuizDTO);
    }

    public function countByTime(Carbon $startTime, Carbon $endTime): array
    {
        return $this->quizzesRepository->countByTime(startTime: $startTime, endTime: $endTime);
    }

    public function countAllByTime(Carbon $startTime, Carbon $endTime): int
    {
        return $this->quizzesRepository->countAllByTime(startTime: $startTime, endTime: $endTime);
    }

    public function totalShareQuiz(Carbon $startTime, Carbon $endTime): int
    {
        return $this->quizzesRepository->totalShareQuiz(startTime: $startTime, endTime: $endTime);
    }
}
