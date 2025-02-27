<?php

namespace App\Http\Controllers;

use App\DTOs\Gamer\CreateGameSettingDTO;
use App\Http\Requests\CreateGameSettingRequest;
use App\Http\Requests\SubmitAnswerRequest;
use App\Http\Requests\SubmitHomeworkRequest;
use App\Services\Interface\GamerServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class GamerController extends Controller
{
    public function __construct(
        private readonly GamerServiceInterface $gamerService
    ) {}

    public function createGameSetting(CreateGameSettingRequest $request): JsonResponse
    {
        try {
            $gamerRoom = $this->gamerService->createGameSetting(
                token: $request->input(key: 'token'),
                gamerId: $request->input(key: 'gamer_id'),
                createGameSettingDTO: new CreateGameSettingDTO(
                    name: $request->input(key: 'name'),
                    isMeme: $request->input(key: 'display_meme')
                )
            );

            return $this->respondWithJson(content: $gamerRoom->toArray());
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function submitAnswer(SubmitAnswerRequest $request): JsonResponse
    {
        try {
            $gamerAnswer = $this->gamerService->submitAnswer(
                token: $request->input(key: 'token'),
                answerId: $request->input(key: 'answer_id')
            );

            return $this->respondWithJson(content: $gamerAnswer->toArray());
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function userOutGame(string $token): JsonResponse
    {
        try {
            $this->gamerService->userOutGame(token: $token);

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function submitHomework(string $token, SubmitHomeworkRequest $request): JsonResponse
    {
        try {
            $this->gamerService->submitHomework(
                token: $token,
                listQuestion: $request->input(key: 'list_question', default: []),
                listAnswer: $request->input(key: 'list_answer', default: []),
                autoSubmit: (bool) $request->input(key: 'auto_submit'),
            );

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }
}
