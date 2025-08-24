<?php

namespace App\Http\Controllers;

use App\DTOs\Quizz\SaveSettingDTO;
use App\Http\Requests\UpdateSettingRequest;
use App\Services\Interface\SettingServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingServiceInterface $settingService
    ) {}

    public function getSetting(string $quizId): JsonResponse
    {
        try {
            $settings = $this->settingService->getSetting(quizId: $quizId);

            return $this->respondWithJson(content: $settings ? $settings->toArray() : []);
        } catch (Throwable $e) {
            Log::error($e);
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function updateSetting(UpdateSettingRequest $request): JsonResponse
    {
        try {
            $setting = new SaveSettingDTO(
                speedPriority: $request->input(key: 'speed_priority'),
                backgroundFile: $request->file(key: 'background'),
                musicFile: $request->file(key: 'music'),
                oldBackground: $request->input(key: 'old_background'),
                oldBackgroundName: $request->input(key: 'old_background_name'),
                oldMusic: $request->input(key: 'old_music'),
                oldMusicName: $request->input(key: 'old_music_name'),
            );
            $this->settingService->updateSetting(
                quizzeIds: $request->input(key: 'quizze_ids'),
                setting: $setting
            );

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function getLatestUpdated(Request $request): JsonResponse
    {
        try {
            $latestUpdated = $this->settingService->getLatestUpdated();

            return $this->respondWithJson(content: $latestUpdated ? $latestUpdated->toArray() : []);
        } catch (Throwable $e) {
            Log::error($e);
            return $this->respondWithJsonError(e: $e);
        }
    }
}
