<?php

namespace App\Repository\Implement;

use App\Models\QuizzeSetting;
use App\Repository\Interface\SettingRepositoryInterface;

readonly class SettingRepository implements SettingRepositoryInterface
{

    public function __construct(
        private QuizzeSetting $quizzeSetting
    ) {
    }

    public function getSetting(?string $quizId): ?QuizzeSetting
    {
        return $this->quizzeSetting->query()->where('quizze_id', $quizId)->first();
    }

    public function deleteAllSetting(): void
    {
        $this->quizzeSetting->query()->truncate();
    }

    public function deleteByIds(Array $ids): void
    {
        $this->quizzeSetting->query()->whereIn('id', $ids)->delete();
    }

    public function insertSetting(Array $settings): void
    {
        $this->quizzeSetting->query()->insert($settings);
    }
}
