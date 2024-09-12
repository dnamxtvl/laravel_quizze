<?php

namespace App\Http\Controllers;

use App\DTOs\User\UserDeviceInformationDTO;
use App\Http\Requests\NextQuestionRequest;
use App\Http\Requests\StartRoomRequest;
use App\Services\Interface\RoomServiceInterface;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class RoomController extends Controller
{
    public function __construct(
        private readonly RoomServiceInterface $roomService
    ) {}

    public function createRoom(string $quizId): JsonResponse
    {
        try {
            $newRoom = $this->roomService->createRoom(quizId: $quizId);

            return $this->respondWithJson(content: $newRoom->toArray());
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function checkValidRoom(string $roomId): JsonResponse
    {
        try {
            $checkRoomValidResponse = $this->roomService->checkValidRoom(roomId: $roomId);

            return $this->respondWithJson(content: $checkRoomValidResponse->toArray());
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function validateRoomCode(Request $request): JsonResponse
    {
        try {
            $ip = $request->ip();
            $location = Location::get(ip: $ip);
            $gamerInfo = new UserDeviceInformationDTO(
                ip: $ip,
                device: $request->header('User-Agent'),
                longitude: $location->longitude ?? null,
                latitude: $location->latitude ?? null,
                country: $location->countryName ?? null,
                city: $location->cityName ?? null
            );
            $gamer = $this->roomService->validateRoomCode(code: $request->input(key: 'code'), gamerInfo: $gamerInfo);

            return $this->respondWithJson(content: $gamer->toArray());
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function listQuestionOfRoom(string $roomToken): JsonResponse
    {
        if (request()->ajax()) {
            return response()->json(data: ['error' => 'Unauthorized'], status: 401);
        }

        try {
            $questions = $this->roomService->listQuestionOfRoom(token: $roomToken);

            return $this->respondWithJson(content: $questions->toArray());
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function startRoom(StartRoomRequest $request): JsonResponse
    {
        try {
            $this->roomService->startRoom(roomId: $request->input(key: 'room_id'));

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function nextQuestion(NextQuestionRequest $request): JsonResponse
    {
        try {
            $this->roomService->nextQuestion(
                roomId: $request->input(key: 'room_id'),
                questionId: $request->input(key: 'question_id')
            );

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function adminEndGame(string $roomId): JsonResponse
    {
        try {
            $this->roomService->adminEndGame(roomId: $roomId);

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }
}
