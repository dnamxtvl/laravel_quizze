<?php

namespace App\Http\Controllers;

use App\DTOs\Quizz\SaveSettingDTO;
use App\Http\Requests\UpdateSettingRequest;
use App\Services\Interface\SettingServiceInterface;
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
                backgrounds: $request->input(key: 'background'),
                musics: $request->input(key: 'music'),
            );
            $this->settingService->updateSetting(quizzeIds: $request->input(key: 'quizze_ids'), setting: $setting);

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }
}
