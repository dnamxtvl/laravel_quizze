<?php

namespace App\Repository\Interface;

use App\Models\QuizzeSetting;

interface SettingRepositoryInterface
{
    public function getSetting(?string $quizId): ?QuizzeSetting;

    public function deleteAllSetting(): void;

    public function deleteByIds(Array $ids): void;

    public function insertSetting(Array $settings): void;
}
