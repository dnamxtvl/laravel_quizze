<?php

namespace App\Console\Commands;

use App\Enums\User\UserRoleEnum;
use App\Models\Quizze;
use App\Models\User;
use App\Repository\Interface\QuizzesRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncQuizzeCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    const DEFAULT_CODE = 10000000;
    const PREPARE_SYS_CODE = 'SY';
    const PREPARE_USER_CODE = 'US';

    protected $signature = 'app:sync-quizze-code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        DB::beginTransaction();
        $this->info('Start sync quizze code');

        try {
            $quizRepository = app()->make(QuizzesRepositoryInterface::class);
            $quizzes = $quizRepository->getAll();

            $allQuizArr = $quizzes->map(function ($item) use ($quizzes) {
                if (!$item->created_by_sys) {
                    $index = $quizzes->where('created_by_sys', false)->where('id', '<', $item->id)->count();
                } else {
                    $index = $quizzes->where('created_by_sys', true)->where('id', '<', $item->id)->count();
                }

                $code = (string)(self::DEFAULT_CODE + $index + 1);

                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'category_id' => $item->category_id,
                    'user_id' => $item->user_id,
                    'created_by_sys' => $item->created_by_sys,
                    'deleted_by' => $item->deleted_by,
                    'code' => !$item->created_by_sys ? (self::PREPARE_USER_CODE . $code) : (self::PREPARE_SYS_CODE . $code),
                    'created_at' => Carbon::parse($item->created_at)->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::parse($item->updated_at)->format('Y-m-d H:i:s'),
                ];
            })->toArray();

            Quizze::query()->upsert($allQuizArr, ['id'], ['code']);
            User::query()->where('type', UserRoleEnum::USER->value)->update(['type' => UserRoleEnum::ADMIN->value]);

            DB::commit();
            $this->info('Finish sync quizze code');
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            Log::error($e);
            DB::rollBack();
        }
    }
}
