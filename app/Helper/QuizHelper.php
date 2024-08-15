<?php

namespace App\Helper;

use App\Enums\Room\RoomStatusEnum;
use App\Repository\Interface\RoomRepositoryInterface;

readonly class QuizHelper
{
    public function __construct(
        private RoomRepositoryInterface $roomRepository
    ) {
    }

    public function generateCode(int $length = 6): int
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= rand(0, 9);
        }

        $code = (int) $code;
        $filters = ['status' => RoomStatusEnum::PREPARE->value];
        $room = $this->roomRepository->findRoomByCode(code: $code, filters: $filters);
        if ($room) {
            return $this->generateCode(length: $length);
        }

        return $code;
    }
}
