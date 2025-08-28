<?php

namespace App\Repository\Interface;

use App\Models\QuizzeSetting;

interface SettingRepositoryInterface
{
    public function getSetting(?string $quizId): ?QuizzeSetting;

    public function deleteAllSetting(): void;

    public function deleteByQuizzeIds(array $ids): void;

    public function insertSetting(array $settings): void;

    public function getLatestUpdated(bool $isAdmin = false): ?QuizzeSetting;
}
