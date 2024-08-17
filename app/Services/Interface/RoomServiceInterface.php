<?php

namespace App\Services\Interface;

use App\DTOs\Room\CheckValidRoomResponseDTO;
use App\DTOs\User\UserDeviceInformationDTO;
use App\DTOs\User\VerifyCodeResponseDTO;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RoomServiceInterface
{
    public function createRoom(string $quizId): Model;

    public function checkValidRoom(string $roomId): CheckValidRoomResponseDTO;

    public function validateRoomCode(int $code, UserDeviceInformationDTO $gamerInfo): VerifyCodeResponseDTO;

    public function listQuestionOfRoom(string $token): array;

    public function startRoom(string $roomId): void;

    public function nextQuestion(string $roomId, int $questionId): void;
}
