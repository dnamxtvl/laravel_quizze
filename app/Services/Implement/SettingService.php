<?php

namespace App\Services\Implement;

use App\DTOs\Quizz\SaveSettingDTO;
use App\Enums\User\UserRoleEnum;
use App\Models\QuizzeSetting;
use App\Repository\Interface\QuizzesRepositoryInterface;
use App\Repository\Interface\SettingRepositoryInterface;
use App\Services\Interface\SettingServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $quizzes = $this->quizzesRepository->getByIds(ids: $quizzeIds);
        if (count($quizzes) != count($quizzeIds)) {
            throw new BadRequestHttpException(message: 'Không tìm thấy bộ cảu hỏi!');
        }

        $authUser = Auth::user();
        foreach ($quizzes as $quizze) {
            if ($quizze->user_id != $authUser->id && $authUser->type == UserRoleEnum::ADMIN->value) {
                throw new BadRequestHttpException(message: 'Bạn không có quyền thực hiện hành động này!');
            }
        }

        DB::beginTransaction();
        try {
            $settings = [];
            foreach ($quizzeIds as $quizzeId) {
                $settings[] = [
                    'quizze_id' => $quizzeId,
                    'speed_priority' => $setting->getSpeedPriority(),
                    'background' => $setting->getBackgrounds(),
                    'music' => $setting->getMusics(),
                    'last_updated_by' => $authUser->id,
                ];
            }

            $this->settingRepository->deleteByIds($quizzeIds);
            $this->settingRepository->insertSetting($settings);
            DB::commit();
        } catch (Throwable $th) {
            Log::error(message: $th->getMessage());
            DB::rollBack();
            throw new InternalErrorException(message: 'Đã xảy ra lỗi không mong muốn!');
        }
    }
}
