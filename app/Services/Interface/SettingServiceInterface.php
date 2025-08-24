<?php

namespace App\Services\Interface;
use App\DTOs\Quizz\SaveSettingDTO;
use App\Models\QuizzeSetting;

interface SettingServiceInterface
{
    public function getSetting(string $quizId): ?QuizzeSetting;

    public function updateSetting(array $quizzeIds, SaveSettingDTO $setting): void;
}
