<?php

namespace App\Console\Commands;

use App\Enums\Room\RoomStatusEnum;
use App\Models\Room;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class DailyCancelRoom extends Command
{
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
        Log::info('Starting daily room cancellation process.');
        try {
            Room::query()->whereNotIn('status', [RoomStatusEnum::CANCELLED->value, RoomStatusEnum::PENDING->value])
                ->where('created_at', '<', now()->subDays(7))->update(['status' => RoomStatusEnum::CANCELLED->value]);
            Log::info('Daily room cancellation process completed successfully.');
        } catch (Throwable $e) {
            Log::error('Error auto cancelling rooms: ' . $e->getMessage());
        }
    }
}
