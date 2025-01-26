<?php

namespace App\Console\Commands;

use App\Enums\User\UserRoleEnum;
use App\Models\Question;
use App\Models\Quizze;
use App\Models\User;
use App\Repository\Implement\QuestionRepository;
use App\Repository\Interface\QuizzesRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
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
    const CHUNK_SIZE = 1000;

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
            $questionRepository = app()->make(QuestionRepository::class);
            $quizzes = $quizRepository->getAll();
            $questions = $questionRepository->getAll();

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

            $allQuestionArr = $questions->map(function ($item) use ($questions) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'index_question' => $item->index_question,
                    'quizze_id' => $item->quizze_id,
                    'created_by_sys' => $item->created_by_sys,
                    'is_old_question' => $item->is_old_question,
                    'content_html' => '<p>' . $item->title . '</p>',
                    'image' => $item->image,
                    'type' => $item->image ? 1 : 0,
                    'updated_by' => $item->updated_by,
                    'updated_by_sys' => $item->updated_by_sys,
                    'created_at' => Carbon::parse($item->created_at)->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::parse($item->updated_at)->format('Y-m-d H:i:s'),
                ];
            })->toArray();

            Quizze::query()->upsert($allQuizArr, ['id'], ['code']);
            collect($allQuestionArr)->chunk(self::CHUNK_SIZE)->each(function (Collection $chunk) {
                Question::query()->upsert($chunk->toArray(), ['id'], ['content_html']);
            });
            User::query()->where('type', UserRoleEnum::USER->value)->update(['type' => UserRoleEnum::ADMIN->value]);

            DB::commit();
            $this->info('Finish sync quizze code');
        } catch (Throwable $e) {
            $this->error('Sync quizze code error');
            Log::error($e);
            DB::rollBack();
        }
    }
}
