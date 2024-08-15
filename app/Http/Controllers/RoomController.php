<?php

namespace App\Http\Controllers;

use App\Services\Interface\RoomServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class RoomController extends Controller
{
    public function __construct(
        private readonly RoomServiceInterface $roomService
    ) {
    }

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
            $room = $this->roomService->checkValidRoom(roomId: $roomId);
            return $this->respondWithJson(content: $room->toArray());
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }
}
