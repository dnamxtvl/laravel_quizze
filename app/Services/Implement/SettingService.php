<?php

namespace App\Services\Implement;

use App\DTOs\Quizz\SaveSettingDTO;
use App\Enums\User\UserRoleEnum;
use App\Exceptions\User\UnAuthorizeException;
use App\Models\QuizzeSetting;
use App\Repository\Interface\QuizzesRepositoryInterface;
use App\Repository\Interface\SettingRepositoryInterface;
use App\Services\Interface\SettingServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

readonly class SettingService implements SettingServiceInterface
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository,
        private QuizzesRepositoryInterface $quizzesRepository,
    ) {}

    public function getSetting(?string $quizId): ?QuizzeSetting
    {
        $quiz = $this->quizzesRepository->findById(quizId: $quizId);
        if (is_null($quiz)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy bộ cảu hỏi!');
        }

        return $this->settingRepository->getSetting($quizId);
    }

    /**
     * @throws InternalErrorException
     * @throws Throwable
     */
    public function updateSetting(array $quizzeIds, SaveSettingDTO $setting): void
    {
        if ($setting->getBackgroundFile()) {
            $path = Storage::disk('s3')->put('avatar', $setting->getBackgroundFile(), 'public');
            $setting->setBackground(background: config('filesystems.disks.s3.url'). '/' . $path);
            $setting->setBackgroundName(backgroundName: $setting->getBackgroundFile()->getClientOriginalName());
        }
        if ($setting->getMusicFile()) {
            $path = Storage::disk('s3')->put('avatar', $setting->getMusicFile(), 'public');
            $setting->setMusic(music: config('filesystems.disks.s3.url'). '/' . $path);
            $setting->setMusicName(musicName: $setting->getMusicFile()->getClientOriginalName());
        }

        $quizzes = $this->quizzesRepository->getByIds(ids: $quizzeIds);
        if (count($quizzes) != count($quizzeIds)) {
            throw new BadRequestHttpException(message: 'Không tìm thấy bộ cảu hỏi!');
        }

        $authUser = Auth::user();
        foreach ($quizzes as $quizze) {
            if (!($quizze->user_id == $authUser->id || !empty($quizze->sharedWithMe)) && $authUser->type == UserRoleEnum::ADMIN->value) {
                throw new UnAuthorizeException(message: 'Bạn không có quyền thực hiện hành động này!');
            }
        }

        DB::beginTransaction();
        try {
            $settings = [];
            foreach ($quizzeIds as $quizzeId) {
                $settings[] = [
                    'quizze_id' => $quizzeId,
                    'speed_priority' => $setting->getSpeedPriority(),
                    'background' => $setting->getBackground() ?? $setting->getOldBackground() ?? null,
                    'background_name' => $setting->getBackgroundName() ?? $setting->getOldBackgroundName() ?? null,
                    'music' => $setting->getMusic() ?? $setting->getOldMusic() ?? null,
                    'music_name' => $setting->getMusicName() ?? $setting->getOldMusicName() ?? null,
                    'last_updated_by' => $authUser->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $this->settingRepository->deleteByQuizzeIds($quizzeIds);
            $this->settingRepository->insertSetting($settings);
            DB::commit();
            Log::info(message: Auth::user()->name . ' đã cập nhật cài đặt cho bộ câu hỏi ' . $quizzes->implode('code', ', '));
        } catch (Throwable $th) {
            Log::error(message: $th->getMessage());
            DB::rollBack();
            throw new InternalErrorException(message: 'Đã xảy ra lỗi không mong muốn!');
        }
    }

    public function getLatestUpdated(): ?QuizzeSetting
    {
        return $this->settingRepository->getLatestUpdated(
            isAdmin: Auth::user()->type == UserRoleEnum::ADMIN->value
        );
    }
}
