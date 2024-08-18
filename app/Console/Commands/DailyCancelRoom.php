<?php

namespace App\Console\Commands;

use App\Enums\Room\RoomStatusEnum;
use App\Models\Room;
use Illuminate\Console\Command;

class DailyCancelRoom extends Command
{
    CONST CHUNK = 100;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:cancel-room';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Room::query()->whereNotIn('status', [RoomStatusEnum::CANCELLED->value, RoomStatusEnum::PENDING->value])
            ->where('created_at', '<', now()->subDay())->chunk(self::CHUNK, function ($rooms) {
                foreach ($rooms as $room) {
                    $room->status = RoomStatusEnum::CANCELLED->value;
                    $room->save();
                }
            });
    }
}
