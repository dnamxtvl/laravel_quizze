<?php

namespace App\Helper;

use App\Enums\Room\RoomStatusEnum;
use App\Repository\Interface\RoomRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

readonly class QuizHelper
{
    public function __construct(
        private RoomRepositoryInterface $roomRepository
    ) {}

    public function generateCode(int $length = 6): int
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= rand(1, 9);
        }

        $code = (int) $code;
        $filters = ['status' => RoomStatusEnum::PREPARE->value];
        $room = $this->roomRepository->findRoomByCode(code: $code, filters: $filters);
        if ($room) {
            return $this->generateCode(length: $length);
        }

        return $code;
    }

    public function scheduleRoomStatusPending(string $roomId, RoomStatusEnum $status, int $timeInterval, string $action = ''): void
    {
        $eventName = 'update_status_'.str_replace('-', '_', $roomId);
        if (Str::length($action) > 0) {
            $eventName = 'update_status_'.str_replace('-', '_', $roomId).'_'.$action;
        }
        $sql = sprintf("
        CREATE EVENT IF NOT EXISTS `%s`  -- Sử dụng backtick để bao tên sự kiện
        ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL %d SECOND
        DO
            UPDATE rooms
            SET status = %d
            WHERE id = '%s';
        ", $eventName, $timeInterval, $status->value, $roomId);

        DB::unprepared($sql);
    }
}
