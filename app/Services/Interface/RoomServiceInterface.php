<?php

namespace App\Services\Interface;

use App\DTOs\Gamer\UserDeviceInformationDTO;
use App\DTOs\Gamer\VerifyCodeResponseDTO;
use App\DTOs\Room\CheckValidRoomResponseDTO;
use App\DTOs\Room\CreateRoomParamsDTO;
use App\DTOs\Room\DetailRoomReportDTO;
use App\DTOs\Room\ListRoomReportParamDTO;
use App\DTOs\Room\QuestionsOfRoomResponseDTO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RoomServiceInterface
{
    public function createRoom(string $quizId, CreateRoomParamsDTO $createRoomParams): Model;

    public function checkValidRoom(string $roomId): CheckValidRoomResponseDTO;

    public function getDetailRoomReport(string $roomId): DetailRoomReportDTO;

    public function getListRoomReport(ListRoomReportParamDTO $listRoomReportParam): LengthAwarePaginator;

    public function validateRoomCode(int $code, UserDeviceInformationDTO $gamerInfo): VerifyCodeResponseDTO;

    public function listQuestionOfRoom(string $token): QuestionsOfRoomResponseDTO;

    public function startRoom(string $roomId): void;

    public function nextQuestion(string $roomId, string $questionId): void;

    public function adminEndGame(string $roomId): void;

    public function deleteReport(string $roomId): void;

    public function countRoom(): int;

    public function groupByYear(Carbon $startTime, Carbon $endTime): Collection;

    public function countByTime(Carbon $startTime, Carbon $endTime): int;
}
