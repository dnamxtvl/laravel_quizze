<?php

namespace App\Services\Implement;

use App\DTOs\Room\CheckValidRoomResponseDTO;
use App\DTOs\Room\CreateRoomParamsDTO;
use App\DTOs\Room\DetailRoomReportDTO;
use App\DTOs\Room\ListRoomReportParamDTO;
use App\DTOs\Room\QuestionsOfRoomResponseDTO;
use App\DTOs\Room\SetNextQuestionRoomDTO;
use App\DTOs\User\CreateGamerTokenDTO;
use App\DTOs\User\UserDeviceInformationDTO;
use App\DTOs\User\VerifyCodeResponseDTO;
use App\Enums\Exception\ExceptionCodeEnum;
use App\Enums\Room\RoomStatusEnum;
use App\Enums\Room\RoomTypeEnum;
use App\Events\AdminEndgameEvent;
use App\Events\NextQuestionEvent;
use App\Events\StartGameEvent;
use App\Exceptions\Admin\UnAuthorizationStartRoomException;
use App\Helper\QuizHelper;
use App\Models\Gamer;
use App\Models\GamerToken;
use App\Models\Room;
use App\Repository\Interface\GamerRepositoryInterface;
use App\Repository\Interface\GamerTokenRepositoryInterface;
use App\Repository\Interface\QuestionRepositoryInterface;
use App\Repository\Interface\QuizzesRepositoryInterface;
use App\Repository\Interface\RoomRepositoryInterface;
use App\Repository\Interface\UserShareQuizRepositoryInterface;
use App\Services\Interface\RoomServiceInterface;
use Carbon\Carbon;
use Dflydev\DotAccessData\Exception\DataException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

readonly class RoomService implements RoomServiceInterface
{
    const MIN_QUESTION = 1;

    public function __construct(
        private QuizHelper $quizHelper,
        private RoomRepositoryInterface $roomRepository,
        private GamerRepositoryInterface $gamerRepository,
        private GamerTokenRepositoryInterface $gamerTokenRepository,
        private QuestionRepositoryInterface $questionRepository,
        private QuizzesRepositoryInterface $quizzesRepository,
        private UserShareQuizRepositoryInterface $userShareQuizRepository,
    ) {}

    public function createRoom(string $quizId, CreateRoomParamsDTO $createRoomParams): Model
    {
        $quiz = $this->quizzesRepository->findById(quizId: $quizId);
        if (is_null($quiz)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy bộ câu hỏi!');
        }

        if ($quiz->user_id != Auth::id()) {
            $this->checkIsUserSharedQuiz(quizId: $quizId);
        }

        $listQuestion = $this->questionRepository->listQuestionOfQuiz(quizId: $quizId)->pluck('id')->toArray();
        $createRoomParams->setQuestionIds(questionIds: $listQuestion);
        $code = $this->quizHelper->generateCode(length: config(key: 'app.quizzes.room_code_length'));
        $newRoom = $this->roomRepository->createRoom(quizId: $quizId, code: $code, createRoomParams: $createRoomParams);

        if ($createRoomParams->getType() == RoomTypeEnum::HOMEWORK) {
            $timeIntervalEnd = abs($createRoomParams->getEndAt()->diffInSeconds(now()));
            $timeIntervalStart = abs($createRoomParams->getStartAt()->diffInSeconds(now()));
            /* @var Room $newRoom */
            $this->quizHelper->scheduleRoomStatusPending(
                roomId: $newRoom->id,
                status: RoomStatusEnum::HAPPENING,
                timeInterval: $timeIntervalStart,
                action: 'set_start'
            );
            $this->quizHelper->scheduleRoomStatusPending(
                roomId: $newRoom->id,
                status: RoomStatusEnum::FINISHED,
                timeInterval: $timeIntervalEnd,
                action: 'set_end'
            );
        }

        return $newRoom;
    }

    public function checkValidRoom(string $roomId): CheckValidRoomResponseDTO
    {
        $room = $this->roomRepository->findById(roomId: $roomId);
        if (is_null($room)) {
            throw new NotFoundHttpException(message: 'Màn chơi không tồn tại!');
        }
        /* @var Room $room */
        if ($room->status == RoomStatusEnum::CANCELLED->value || $room->status == RoomStatusEnum::FINISHED->value) {
            throw new BadRequestHttpException(message: 'Màn chơi đã kết thúc trước đó!', code: ExceptionCodeEnum::ROOM_CANCELLED->value);
        }
        $questions = $this->getQuestionByRoom(room: $room);
        $gamers = $room->gamers()->withSum('gamerAnswers', 'score')
            ->with('gamerAnswers')
            ->orderBy('gamer_answers_sum_score', 'desc')
            ->get();
        $timeRemaining = (int) now()->diffInSeconds(Carbon::parse($room->current_question_end_at));

        return new CheckValidRoomResponseDTO(room: $room, questions: $questions, gamers: $gamers, timeRemaining: $timeRemaining);
    }

    public function validateRoomCode(int $code, UserDeviceInformationDTO $gamerInfo): VerifyCodeResponseDTO
    {
        $room = $this->roomRepository->findRoomByCode(code: $code);
        /* @var Room $room */
        if (is_null($room) || ($room->status != RoomStatusEnum::PREPARE->value && $room->type == RoomTypeEnum::KAHOOT->value)
            || ($room->status == RoomStatusEnum::FINISHED->value && $room->type == RoomTypeEnum::HOMEWORK->value)) {
            throw new NotFoundHttpException(message: 'Mã code không hợp lệ!');
        }
        DB::beginTransaction();
        try {
            $gamer = $this->gamerRepository->createGamer(gamerInfo: $gamerInfo);
            $tokenExpired = Carbon::parse($room->created_at)->addDay();
            /* @var Gamer $gamer */
            $gamerTokenDTO = new CreateGamerTokenDTO(
                gamerId: $gamer->id,
                token: hash('sha256', ($gamer->id.$room->id.($gamerInfo->getIp() ?? Str::uuid()))),
                roomId: $room->id,
                expiredAt: $tokenExpired,
            );
            $gamerToken = $this->gamerTokenRepository->createGamerToken(gamerTokenDTO: $gamerTokenDTO);
            DB::commit();

            /* @var GamerToken $gamerToken */
            return new VerifyCodeResponseDTO(
                gamerId: $gamer->id,
                token: $gamerToken->token,
                expiredAt: $tokenExpired,
            );
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(message: $e->getMessage());
            throw new DataException(message: 'Có lỗi xảy ra, vui lòng thử lại sau!', code: ExceptionCodeEnum::CREATE_GAMER_FAIL->value);
        }
    }

    public function listQuestionOfRoom(string $token): QuestionsOfRoomResponseDTO
    {
        $gamerToken = $this->gamerTokenRepository->getQuery(filters: ['token' => $token])->first();
        if (is_null($gamerToken) || $gamerToken->expired_at < now()) {
            throw new NotFoundHttpException(message: 'Token không hợp lệ!', code: ExceptionCodeEnum::INVALID_GAME_TOKEN->value);
        }

        $room = $gamerToken->room;

        $gamer = $gamerToken->gamer()
            ->withSum('gamerAnswers', 'score')
            ->with('gamerAnswers')
            ->first();

        if (is_null($gamer) || is_null($room) || $room->status == RoomStatusEnum::CANCELLED->value) {
            throw new NotFoundHttpException(message: 'Room không hợp lệ!', code: ExceptionCodeEnum::INVALID_ROOM->value);
        }

        if ($room->type == RoomTypeEnum::KAHOOT->value && $room->status == RoomStatusEnum::FINISHED->value) {
            throw new BadRequestHttpException(message: 'Môn chơi đã kết thúc!', code: ExceptionCodeEnum::ROOM_FINISHED->value);
        }

        $questions = $this->getQuestionByRoom(room: $room);
        $timeRemaining = $room->type == RoomTypeEnum::HOMEWORK->value ? (int) now()->diffInSeconds(Carbon::parse($room->ended_at)) :
            (int) now()->diffInSeconds(Carbon::parse($room->current_question_end_at));

        return new QuestionsOfRoomResponseDTO(
            room: $room,
            questions: $questions,
            gamer: $gamer,
            timeRemaining: $timeRemaining,
            gamerToken: $gamerToken,
        );
    }

    public function startRoom(string $roomId): void
    {
        $room = $this->roomRepository->findById(roomId: $roomId);
        /* @var Room $room */
        if (is_null($room) || $room->status != RoomStatusEnum::PREPARE->value) {
            throw new NotFoundHttpException(message: 'Màn chơi không tồn tại! hoặc đã được kích hoạt trước đó!');
        }

        $quiz = $room->quizze;
        if (is_null($quiz)) {
            throw new NotFoundHttpException(message: 'Quizz đã bị xóa!');
        }

        $questions = $this->questionRepository->listQuestionOfQuiz(quizId: $quiz->id);
        $countQuestion = $questions->count();
        if (! $countQuestion) {
            throw new NotFoundHttpException(message: 'Không tìm thấy câu hỏi nào!');
        }

        if ($quiz->user_id != Auth::id()) {
            $this->checkIsUserSharedQuiz(quizId: $quiz->id);
        }

        if (! $room->gamerTokens->count()) {
            throw new BadRequestHttpException(message: 'Chưa có user tham gia!');
        }

        $now = now();
        $roomStatus = $countQuestion == self::MIN_QUESTION ? RoomStatusEnum::PREPARE_FINISH : RoomStatusEnum::PENDING;
        $timeInterval = (int) config('app.quizzes.time_reply');
        $setNextQuestionRoomDTO = new SetNextQuestionRoomDTO(
            currentQuestionId: $questions->first()->id,
            currentQuestionStartAt: $now,
            currentQuestionEndAt: $now->addSeconds(value: config(key: 'app.quizzes.time_reply')),
            status: RoomStatusEnum::HAPPENING,
            startAt: $now,
        );

        $this->roomRepository->updateRoomAfterNextQuestion(room: $room, nextQuestionRoomDTO: $setNextQuestionRoomDTO);
        if ($room->type == RoomTypeEnum::KAHOOT->value) {
            $this->quizHelper->scheduleRoomStatusPending(roomId: $room->id, status: $roomStatus, timeInterval: $timeInterval);
        }
        broadcast(new StartGameEvent(roomId: $room->id))->toOthers();
    }

    private function getQuestionByRoom(Room $room): Collection
    {
        $questions = $this->questionRepository->listQuestionByIds(questionIds: json_decode($room->list_question));
        if (! $questions->count() || is_null($room->quizze)) {
            throw new NotFoundHttpException(message: 'Bộ câu hỏi đã bị xóa!', code: ExceptionCodeEnum::NON_QUESTION->value);
        }

        foreach ($questions as $question) {
            $answers = $question->answers;
            if (count($answers) < config('app.quizzes.min_answer')) {
                throw new InvalidArgumentException(message: 'Bộ câu hỏi không hợp lệ!', code: ExceptionCodeEnum::NON_QUESTION->value);
            }

            $countCorrectAnswer = collect($answers)->where('is_correct', true)->count();
            if ($countCorrectAnswer === 0) {
                throw new InvalidArgumentException(message: 'Bộ câu hỏi không hợp lệ!', code: ExceptionCodeEnum::NON_ANSWER_CORRECT->value);
            }
        }

        return $questions;
    }

    /**
     * @throws InternalErrorException
     */
    public function nextQuestion(string $roomId, string $questionId): void
    {
        $now = now();
        try {
            $room = $this->roomRepository->findById(roomId: $roomId);
            /* @var Room $room */
            if (is_null($room)) {
                throw new NotFoundHttpException(message: 'Màn chơi không tồn tại hoặc không ở trạng thái diễn ra!');
            }

            if ($room->status != RoomStatusEnum::PENDING->value) {
                throw new BadRequestHttpException(message: 'Đang trong thời gian trả lời câu hỏi!');
            }

            $quiz = $room->quizze;
            if (is_null($quiz)) {
                throw new NotFoundHttpException(message: 'Quizz đã bị xóa!');
            }

            if ($quiz->user_id != Auth::id()) {
                $this->checkIsUserSharedQuiz(quizId: $quiz->id);
            }

            $nextQuestion = $this->questionRepository->findNextQuestion(quzId: $room->quizze_id, questionId: $questionId);
            if (is_null($nextQuestion)) {
                throw new BadRequestHttpException(message: 'Bạn đang ở câu hỏi cuối cùng!');
            }
            $setNextQuestionRoomDTO = new SetNextQuestionRoomDTO(
                currentQuestionId: $nextQuestion->id,
                currentQuestionStartAt: $now,
                currentQuestionEndAt: $now->addSeconds(value: config(key: 'app.quizzes.time_reply')),
                status: RoomStatusEnum::HAPPENING,
            );
            $this->roomRepository->updateRoomAfterNextQuestion(room: $room, nextQuestionRoomDTO: $setNextQuestionRoomDTO);
            $status = is_null($this->questionRepository->findNextQuestion(quzId: $room->quizze_id, questionId: $nextQuestion->id)) ?
                RoomStatusEnum::PREPARE_FINISH : RoomStatusEnum::PENDING;
            $this->quizHelper->scheduleRoomStatusPending(roomId: $room->id, status: $status, timeInterval: (int) config('app.quizzes.time_reply'));
            broadcast(new NextQuestionEvent(roomId: $room->id, questionId: $nextQuestion->id))->toOthers();
        } catch (Throwable $e) {
            Log::error(message: $e->getMessage());
            throw new InternalErrorException(message: 'Có lỗi xảy ra, vui lòng thử lại sau!');
        }
    }

    public function adminEndGame(string $roomId): void
    {
        $room = $this->roomRepository->findById(roomId: $roomId);
        /* @var Room $room */
        if (is_null($room)) {
            throw new NotFoundHttpException(message: 'Màn chơi không tồn tại!');
        }

        if ($room->status == RoomStatusEnum::FINISHED->value || $room->status == RoomStatusEnum::CANCELLED->value) {
            throw new BadRequestHttpException(message: 'Màn chơi đã kết thúc trước đó!');
        }

        if (Auth::id() != $room->quizze->user_id) {
            $this->checkIsUserSharedQuiz(quizId: $room->quizze_id);
        }

        $room->status = RoomStatusEnum::FINISHED->value;
        $room->ended_at = now();
        $room->save();
        broadcast(new AdminEndgameEvent(roomId: $room->id))->toOthers();
    }

    public function getDetailRoomReport(string $roomId): DetailRoomReportDTO
    {
        $room = $this->roomRepository->findById(roomId: $roomId);
        if (is_null($room)) {
            throw new NotFoundHttpException(message: 'Màn chơi không tồn tại!');
        }
        /* @var Room $room */
        $questions = $this->getQuestionByRoom(room: $room);
        $gamers = $room->gamers()->withSum('gamerAnswers', 'score')
            ->with(['gamerAnswers', 'gamerToken'])
            ->orderBy('gamer_answers_sum_score', 'desc')
            ->get();

        return new DetailRoomReportDTO(room: $room, questions: $questions, gamers: $gamers);
    }

    public function getListRoomReport(ListRoomReportParamDTO $listRoomReportParam): LengthAwarePaginator
    {
        return $this->roomRepository->getListRoomByAdminId(
            userId: Auth::id(),
            page: $listRoomReportParam->getPage(),
            filters: $listRoomReportParam->toArray()
        );
    }

    /**
     * @throws InternalErrorException
     */
    public function deleteReport(string $roomId): void
    {
        $room = $this->roomRepository->findById(roomId: $roomId);
        if (is_null($room)) {
            throw new NotFoundHttpException(message: 'Màn chơi không tồn tại!');
        }
        /* @var Room $room */
        if ($room->status != RoomStatusEnum::FINISHED->value && $room->status != RoomStatusEnum::CANCELLED->value) {
            throw new BadRequestHttpException(message: 'Môn chơi chưa kết thúc, không thể xóa!');
        }

        DB::beginTransaction();
        try {
            $this->roomRepository->deleteRoom(room: $room);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(message: $e->getMessage());
            throw new InternalErrorException(message: 'Có lỗi xảy ra!');
        }
    }

    public function checkIsUserSharedQuiz(string $quizId): void
    {
        $isShared =  $this->userShareQuizRepository->getQuery(filters: ['quizze_id' => $quizId, 'receiver_id' => Auth::id()])
            ->where('is_accept', true)->exists();

        if (!$isShared) {
            throw new UnAuthorizationStartRoomException(
                message: 'Bạn không có quyền thực hiện hành động này!',
                code: ExceptionCodeEnum::NOT_PERMISSION_END_ROOM->value
            );
        }
    }
}
