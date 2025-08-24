<?php

namespace App\Services\Implement;

use App\DTOs\Gamer\CreateGameSettingDTO;
use App\DTOs\Gamer\SaveAnswerDTO;
use App\Enums\Exception\ExceptionCodeEnum;
use App\Enums\Room\RoomStatusEnum;
use App\Enums\Room\RoomTypeEnum;
use App\Events\UserJoinRoomEvent;
use App\Models\Answer;
use App\Models\Gamer;
use App\Repository\Interface\AnswerRepositoryInterface;
use App\Repository\Interface\GamerRepositoryInterface;
use App\Repository\Interface\GamerTokenRepositoryInterface;
use App\Services\Interface\GamerServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

readonly class GamerService implements GamerServiceInterface
{
    public function __construct(
        private GamerRepositoryInterface $gamerRepository,
        private GamerTokenRepositoryInterface $gamerTokenRepository,
        private AnswerRepositoryInterface $answerRepository,
    ) {}

    public function createGameSetting(string $token, string $gamerId, CreateGameSettingDTO $createGameSettingDTO): Model
    {
        $gamer = $this->gamerRepository->findById(gamerId: $gamerId);
        /* @var Gamer $gamer */
        if (is_null($gamer) || is_null($gamer->gamerToken) || $gamer->gamerToken->token !== $token || $gamer->gamerToken->expired_at < now()) {
            throw new BadRequestHttpException(message: 'Token không hợp lệ!', code: ExceptionCodeEnum::INVALID_GAME_TOKEN->value);
        }

        $gamer->name = $createGameSettingDTO->getName();
        $gamer->display_meme = $createGameSettingDTO->getIsMeme();
        $gamer->save();
        broadcast(new UserJoinRoomEvent(roomId: $gamer->gamerToken->room_id, gamer: $gamer))->toOthers();
        $room = $gamer->gamerToken->room;
        Log::info('user ' . $createGameSettingDTO->getName() . ' đã tham gia room ' . $room->code);;

        return $room;
    }

    public function submitAnswer(string $token, int $answerId): Model
    {
        $now = now();
        $gamerToken = $this->gamerTokenRepository->getQuery(filters: ['token' => $token])->first();
        if (is_null($gamerToken) || $gamerToken->expired_at < now()) {
            throw new NotFoundHttpException(message: 'Token không hợp lệ hoặc đã hết hạn!', code: ExceptionCodeEnum::INVALID_GAME_TOKEN->value);
        }
        $room = $gamerToken->room;
        $gamer = $gamerToken->gamer;
        if (is_null($gamer) || is_null($room)) {
            throw new NotFoundHttpException(message: 'Đã xảy ra lỗi không mong muốn!', code: ExceptionCodeEnum::INVALID_ROOM->value);
        }

        if ($room->status != RoomStatusEnum::HAPPENING->value ||
            ($room->current_question_end_at && $now->gt(Carbon::parse($room->current_question_end_at)))) {
            throw new BadRequestHttpException(
                message: 'Đã hết thời gian trả lời!',
                code: ExceptionCodeEnum::EXPIRED_QUESTION->value
            );
        }

        $answer = $this->answerRepository->findById(answerId: $answerId);
        if (is_null($answer)) {
            throw new NotFoundHttpException(message: 'Câu trả lời không tồn tại!', code: ExceptionCodeEnum::NOT_FOUND_ANSWER->value);
        }
        /* @var Answer $answer */
        $isExistGamerAnswer = $this->answerRepository->getQuery(
            filters: ['gamer_id' => $gamer->id, 'question_id' => $answer->question_id]
        )->exists();
        $diffInMilliseconds = 0;
        $score = $answer->is_correct;

        if ($room->type == RoomTypeEnum::KAHOOT->value) {
            if ($isExistGamerAnswer) {
                throw new BadRequestHttpException(
                    message: 'Bạn đã trả lời câu hỏi này rồi!',
                    code: ExceptionCodeEnum::EXIST_GAMER_ANSWER->value
                );
            }
            if ($room->current_question_id != $answer->question_id) {
                throw new BadRequestHttpException(
                    message: 'Câu trả lời không hợp lệ!',
                    code: ExceptionCodeEnum::INVALID_ANSWER->value
                );
            }
            $maxTime = (!empty($answer->question->time_reply) ? (int) $answer->question->time_reply : (int) config(key: 'app.quizzes.time_reply')) * 1000;
            $maxScore = (int) config(key: 'app.quizzes.max_score');
            $diffInMilliseconds = (int) now()->diffInMilliseconds(Carbon::parse($room->current_question_end_at));
            $minScore = (int) config('app.quizzes.min_score');
            $calculatedScore = abs(($diffInMilliseconds / $maxTime) * $maxScore);
            $score = 0;
            if ($answer->is_correct) {
                $score = max($calculatedScore, $minScore);
                if (! empty($room->room_settings)) {
                    $settings = json_decode($room->room_settings, true);
                    if (isset($settings['speed_priority'])) {
                        $score = $maxScore - ($maxScore - $calculatedScore) * ((int) $settings['speed_priority'] / 100);
                    }
                }
            }
        }

        $saveAnswerDTO = new SaveAnswerDTO(
            gamerId: $gamer->id,
            questionId: $answer->question_id,
            answerId: $answerId,
            roomId: $room->id,
            answerInTime: $diffInMilliseconds,
            score: $score,
            roomType: RoomTypeEnum::tryFrom($room->type)
        );
        Log::info($gamer->name . ' vừa trả lời câu hỏi room ' . $room->code);

        return $this->answerRepository->saveAnswer(saveAnswer: $saveAnswerDTO, isUpdate: $isExistGamerAnswer);
    }

    public function userOutGame(string $token): void
    {
        $gamerToken = $this->gamerTokenRepository->getQuery(filters: ['token' => $token])->first();
        if (is_null($gamerToken) || $gamerToken->expired_at < now()) {
            throw new NotFoundHttpException(message: 'Token không hợp lệ hoặc đã hết hạn!', code: ExceptionCodeEnum::INVALID_GAME_TOKEN->value);
        }
        $gamerToken->expired_at = now();
        $gamerToken->save();
        Log::info($gamerToken->gamer->name . ' vừa thoát room ' . $gamerToken->room->code);
    }

    /**
     * @throws InternalErrorException
     */
    public function submitHomework(string $token, array $listQuestion, array $listAnswer, bool $autoSubmit = false): void
    {
        $gamerToken = $this->gamerTokenRepository->getQuery(filters: ['token' => $token])->first();
        if (is_null($gamerToken) || $gamerToken->expired_at < now()) {
            throw new NotFoundHttpException(message: 'Token không hợp lệ hoặc đã hết hạn!', code: ExceptionCodeEnum::INVALID_GAME_TOKEN->value);
        }

        if (! is_null($gamerToken->submit_at)) {
            throw new BadRequestHttpException(message: 'Bạn đã nộp bài trước đó rồi!');
        }

        $room = $gamerToken->room;
        if (! $autoSubmit && (now()->gt(Carbon::parse($room->ended_at)) || $room->status != RoomStatusEnum::HAPPENING->value)) {
            throw new BadRequestHttpException(
                message: 'Đã quá thời gian nộp bào!',
                code: ExceptionCodeEnum::EXPIRED_QUESTION->value
            );
        }

        $scoreAnswer = $this->answerRepository->getScoreByAnswerIds(answerIds: $listAnswer);
        if (count($scoreAnswer) != count($listAnswer)) {
            throw new InternalErrorException(message: 'Bộ câu hỏi không hợp lệ!');
        }

        DB::beginTransaction();
        try {
            $gamerToken->submit_at = now();
            $gamerToken->save();
            $this->answerRepository->updateResultExam(
                listQuestion: $listQuestion,
                listAnswer: $scoreAnswer,
                gamerId: $gamerToken->gamer_id,
                roomId: $room->id
            );

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(message: $e->getMessage());
            throw new InternalErrorException(message: 'Có lỗi xảy ra!');
        }
    }

    public function countGamer(): int
    {
        return $this->gamerRepository->countAll();
    }

    public function groupByYear(Carbon $startTime, Carbon $endTime): Collection
    {
        return $this->gamerRepository->groupByYear(startTime: $startTime, endTime: $endTime);
    }

    public function countByTime(Carbon $startTime, Carbon $endTime): int
    {
        return $this->gamerRepository->countByTime(startTime: $startTime, endTime: $endTime);
    }
}
