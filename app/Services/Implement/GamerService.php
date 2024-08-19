<?php

namespace App\Services\Implement;

use App\DTOs\Gamer\SaveAnswerDTO;
use App\DTOs\User\CreateGameSettingDTO;
use App\Enums\Exception\ExceptionCodeEnum;
use App\Enums\Room\RoomStatusEnum;
use App\Events\UserJoinRoomEvent;
use App\Models\Answer;
use App\Models\Gamer;
use App\Repository\Interface\AnswerRepositoryInterface;
use App\Repository\Interface\GamerRepositoryInterface;
use App\Repository\Interface\GamerTokenRepositoryInterface;
use App\Services\Interface\GamerServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class GamerService implements GamerServiceInterface
{
    public function __construct(
        private GamerRepositoryInterface $gamerRepository,
        private GamerTokenRepositoryInterface $gamerTokenRepository,
        private AnswerRepositoryInterface $answerRepository,
    ) {
    }
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
        Log::info('Room ' . $gamer->gamerToken->room_id);
        broadcast(new UserJoinRoomEvent(roomId: $gamer->gamerToken->room_id, userId: $gamer->id, username: $gamer->name))->toOthers();

        return $gamer;
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

        if ($room->status != RoomStatusEnum::HAPPENING->value || $now->gt(Carbon::parse($room->current_question_end_at))) {
            throw new BadRequestHttpException(
                message: 'Đã hết thời gian trả lời câu hỏi này, vui lòng đợi admin bấm chuyển sang câu tiếp theo!',
                code: ExceptionCodeEnum::EXPIRED_QUESTION->value
            );
        }

        $answer = $this->answerRepository->findById(answerId: $answerId);
        if (is_null($answer)) {
            throw new NotFoundHttpException(message: 'Câu trả lời không tồn tại!', code: ExceptionCodeEnum::NOT_FOUND_ANSWER->value);
        }
        /* @var Answer $answer */
        if ($room->current_question_id != $answer->question_id) {
            throw new BadRequestHttpException(message: 'Câu trả lời không hợp lệ!', code: ExceptionCodeEnum::INVALID_ANSWER->value);
        }

        $isExistGamerAnswer = $this->answerRepository->getQuery(filters: ['gamer_id' => $gamer->id, 'question_id' => $room->current_question_id])->exists();
        if ($isExistGamerAnswer) {
            throw new BadRequestHttpException(message: 'Bạn đã trả lời câu hỏi này rồi!', code: ExceptionCodeEnum::EXIST_GAMER_ANSWER->value);
        }

        $maxTime = ((int) config(key: 'app.quizzes.time_reply')) * 1000;
        $maxScore = (int) config(key: 'app.quizzes.max_score');
        $diffInMilliseconds = (int) now()->diffInMilliseconds(Carbon::parse($room->current_question_start_at));
        /* @var Answer $answer */
        $score = $answer->is_correct ? abs((($diffInMilliseconds / $maxTime)) * $maxScore) : 0;
        Log::info('diffInMilliseconds: ' . $diffInMilliseconds);
        Log::info('score: ' . $score);
        Log::info('maxTime: ' . $maxTime);
        Log::info('maxScore: ' . $maxScore);
        $saveAnswerDTO = new SaveAnswerDTO(
            gamerId: $gamer->id,
            questionId: $room->current_question_id,
            answerId: $answerId,
            roomId: $room->id,
            answerInTime: $diffInMilliseconds,
            score: $score
        );

        return $this->answerRepository->saveAnswer(saveAnswer: $saveAnswerDTO);
    }

    public function userOutGame(string $token): void
    {
        $gamerToken = $this->gamerTokenRepository->getQuery(filters: ['token' => $token])->first();
        if (is_null($gamerToken) || $gamerToken->expired_at < now()) {
            throw new NotFoundHttpException(message: 'Token không hợp lệ hoặc đã hết hạn!', code: ExceptionCodeEnum::INVALID_GAME_TOKEN->value);
        }
        $gamerToken->expired_at = now();
        $gamerToken->save();
    }
}
