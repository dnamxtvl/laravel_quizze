<?php

namespace App\Services\Implement;

use App\Enums\Exception\ExceptionCodeEnum;
use App\Enums\Room\RoomStatusEnum;
use App\Helper\QuizHelper;
use App\Models\Room;
use App\Repository\Interface\RoomRepositoryInterface;
use App\Services\Interface\RoomServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class RoomService implements RoomServiceInterface
{
    public function __construct(
        private QuizHelper $quizHelper,
        private RoomRepositoryInterface $roomRepository
    ) {
    }

    public function createRoom(string $quizId): Model
    {
        $code = $this->quizHelper->generateCode(length: config(key: 'app.quizzes.room_code_length'));
        return $this->roomRepository->createRoom(quizId: $quizId, code: $code);
    }

    public function checkValidRoom(string $roomId): Model
    {
        $room = $this->roomRepository->findById(roomId: $roomId);
        if (is_null($room)) {
            throw new NotFoundHttpException(message: 'Màn chơi không tồn tại!');
        }
        /* @var Room $room */
        if ($room->status == RoomStatusEnum::CANCELLED->value) {
            throw new BadRequestHttpException(message: 'Màn chơi đã cancel trước đó!', code: ExceptionCodeEnum::ROOM_CANCELLED->value);
        }

        return $room;
    }
}
