<?php

namespace App\Console\Commands;

use App\Enums\User\UserRoleEnum;
use App\Models\Category;
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

    const CATEGORY = [
        0 => ["name" => "Kiến thức tổng hợp", "icon" => "ti-book", "color" => "#FF5733", "bg_icon" => "#ea5455"],
        1 => ["name" => "Công nghệ", "icon" => "ti-brand-github", "color" => "#33FF57", "bg_icon" => "#28c76f"],
        2 => ["name" => "Khoa học máy tính", "icon" => "ti-server", "color" => "#3357FF", "bg_icon" => "#33C3FF"],
        3 => ["name" => "Lập trình", "icon" => "ti-device-desktop", "color" => "#FF33A1", "bg_icon" => "#28c76f"],
        4 => ["name" => "Trí tuệ nhân tạo", "icon" => "ti-robot", "color" => "#A133FF", "bg_icon" => "#28c76f"],
        5 => ["name" => "Kỹ thuật", "icon" => "ti-settings", "color" => "#33FFF5", "bg_icon" => "#33C3FF"],
        6 => ["name" => "Kỹ năng mềm", "icon" => "ti-shield", "color" => "#FFC333", "bg_icon" => "#FFA133"],
        7 => ["name" => "Kinh tế", "icon" => "ti-coin", "color" => "#33FF57", "bg_icon" => "#33FFC3"],
        8 => ["name" => "Công nghệ sinh học", "icon" => "ti-logic-buffer", "color" => "#3357FF", "bg_icon" => "#33C3FF"],
        9 => ["name" => "Kế toán", "icon" => "ti-calculator", "color" => "#FF5733", "bg_icon" => "#ea5455"],
        10 => ["name" => "Tài chính", "icon" => "ti-building-bank", "color" => "#33FF57", "bg_icon" => "#28c76f"],
        11 => ["name" => "Ngân hàng", "icon" => "ti-wallet", "color" => "#3357FF", "bg_icon" => "#33C3FF"],
        12 => ["name" => "Quản trị kinh doanh", "icon" => "ti-briefcase", "color" => "#FF33A1", "bg_icon" => "#28c76f"],
        13 => ["name" => "Marketing", "icon" => "ti-headphones", "color" => "#A133FF", "bg_icon" => "#28c76f"],
        14 => ["name" => "Thương mại điện tử", "icon" => "ti-shopping-cart", "color" => "#33FFF5", "bg_icon" => "#33C3FF"],
        15 => ["name" => "Quản lý nhân sự", "icon" => "ti-user", "color" => "#FFC333", "bg_icon" => "#FFA133"],
        16 => ["name" => "Pháp luật", "icon" => "ti-gavel", "color" => "#33FF57", "bg_icon" => "#33FFC3"],
        17 => ["name" => "Chính trị", "icon" => "ti-flag", "color" => "#3357FF", "bg_icon" => "#33C3FF"],
        18 => ["name" => "Giáo dục", "icon" => "ti-world", "color" => "#FF33A1", "bg_icon" => "#28c76f"],
        19 => ["name" => "Toán học", "icon" => "ti-math", "color" => "#A133FF", "bg_icon" => "#28c76f"],
        20 => ["name" => "Vật lý", "icon" => "ti-magnet", "color" => "#33FFF5", "bg_icon" => "#33C3FF"],
        21 => ["name" => "Hóa học", "icon" => "ti-hexagon", "color" => "#FFC333", "bg_icon" => "#ea5455"],
        22 => ["name" => "Sinh học", "icon" => "ti-virus", "color" => "#33FF57", "bg_icon" => "#33FFC3"],
        23 => ["name" => "Y học", "icon" => "ti-vaccine", "color" => "#33FF57", "bg_icon" => "#33FFC3"],
        24 => ["name" => "Lịch sử", "icon" => "ti-history", "color" => "#3357FF", "bg_icon" => "#33C3FF"],
    ];

    const MUSIC = [
          [
              'name' => 'Nhac-xuan1',
              'link' => 'https://namdv-storage.s3.ap-southeast-2.amazonaws.com/background/ConBuomXuan-HoQuangHieu-2577880.mp3',
              'is_default' => 0
          ],
       [
            'name' => 'Nhac-xuan2',
            'link' => 'https://namdv-storage.s3.ap-southeast-2.amazonaws.com/background/NuCuoiXuanDaiMeoRemix-HuongLyYUNIBOODaiMeo-13689508.mp3',
            'is_default' => 0
        ],
       [
            'name' => 'Lobby-classic-halloween',
            'link' => 'https://namdv-storage.s3.ap-southeast-2.amazonaws.com/background/lobby-classic-game-halloween.mp3',
            'is_default' => 1
        ],
        [
            'name' => 'Lobby-classic',
            'link' => 'https://namdv-storage.s3.ap-southeast-2.amazonaws.com/background/lobby-classic-game.mp3',
            'is_default' => 0
        ],
    ];

    const BACKGROUND = [
        [
            'name' => 'Autumn',
            'link' => 'https://namdv-storage.s3.ap-southeast-2.amazonaws.com/background/autumn.jpeg',
            'is_default' => 0
        ],
        [
            'name' => 'Beach',
            'link' => 'https://namdv-storage.s3.ap-southeast-2.amazonaws.com/background/beach.png',
            'is_default' => 0
        ],
        [
            'name' => 'Love',
            'link' => 'https://namdv-storage.s3.ap-southeast-2.amazonaws.com/background/love.webp',
            'is_default' => 0
        ],
        [
            'name' => 'Racing',
            'link' => 'https://namdv-storage.s3.ap-southeast-2.amazonaws.com/background/racing.jpeg',
            'is_default' => 0
        ],
        [
            'name' => 'Spring',
            'link' => 'https://namdv-storage.s3.ap-southeast-2.amazonaws.com/background/spring.jpeg',
            'is_default' => 0
        ],
        [
            'name' => 'Standard',
            'link' => 'https://namdv-storage.s3.ap-southeast-2.amazonaws.com/background/standard.webp',
            'is_default' => 0
        ],
        [
            'name' => 'Summer',
            'link' => 'https://namdv-storage.s3.ap-southeast-2.amazonaws.com/background/summer.webp',
            'is_default' => 0
        ],
        [
            'name' => 'Volleyball',
            'link' => 'https://namdv-storage.s3.ap-southeast-2.amazonaws.com/background/volleyball.jpeg',
            'is_default' => 0
        ],
        [
            'name' => 'Winter',
            'link' => 'https://namdv-storage.s3.ap-southeast-2.amazonaws.com/background/winter.jpeg',
            'is_default' => 1
        ],
    ];

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
        $this->info('Start sync quizze code');
        Category::query()->truncate();

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

            $categories = [];
            foreach (self::CATEGORY as $key => $value) {
                $categories[] = [
                    'name' => $value['name'],
                    'icon' => $value['icon'],
                    'color_icon' => $value['color'],
                    'bg_icon' => $value['bg_icon'],
                    'parent_id' => 0,
                    'music' => json_encode(self::MUSIC),
                    'background' => json_encode(self::BACKGROUND),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::beginTransaction();

            Quizze::query()->upsert($allQuizArr, ['id'], ['code']);
            collect($allQuestionArr)->chunk(self::CHUNK_SIZE)->each(function (Collection $chunk) {
                Question::query()->upsert($chunk->toArray(), ['id'], ['content_html']);
            });
            User::query()->where('type', UserRoleEnum::USER->value)->update(['type' => UserRoleEnum::ADMIN->value]);
            Category::query()->insert($categories);

            DB::commit();
            $this->info('Finish sync quizze code');
        } catch (Throwable $e) {
            $this->error('Sync quizze code error');
            Log::error($e);
            DB::rollBack();
        }
    }
}
