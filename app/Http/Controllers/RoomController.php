<?php

namespace App\Http\Controllers;

use App\DTOs\Gamer\UserDeviceInformationDTO;
use App\DTOs\Room\CreateRoomParamsDTO;
use App\DTOs\Room\ListRoomReportParamDTO;
use App\Enums\Room\RoomStatusEnum;
use App\Enums\Room\RoomTypeEnum;
use App\Http\Requests\AdminCreateRoomRequest;
use App\Http\Requests\GetListRoomReportRequest;
use App\Http\Requests\NextQuestionRequest;
use App\Http\Requests\StartRoomRequest;
use App\Services\Interface\RoomServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Facades\Location;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class RoomController extends Controller
{
    const DEFAULT_PAGE = 1;

    public function __construct(
        private readonly RoomServiceInterface $roomService
    ) {}

    public function createRoom(string $quizId, AdminCreateRoomRequest $request): JsonResponse
    {
        try {
            $createRoomParams = new CreateRoomParamsDTO(
                type: RoomTypeEnum::tryFrom($request->input(key: 'type')),
                startAt: Carbon::parse($request->input(key: 'start_time')),
                endAt: Carbon::parse($request->input(key: 'end_time')),
            );
            $newRoom = $this->roomService->createRoom(quizId: $quizId, createRoomParams: $createRoomParams);

            return $this->respondWithJson(content: $newRoom->toArray());
        } catch (Throwable $e) {
            Log::error($e);
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

    public function getDetailRoomReport(string $roomId): JsonResponse
    {
        try {
            $roomDetail = $this->roomService->getDetailRoomReport(roomId: $roomId);

            return $this->respondWithJson(content: $roomDetail->toArray());
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }

    public function getListRoomReport(GetListRoomReportRequest $request): JsonResponse
    {
        try {
            $paramFilter = new ListRoomReportParamDTO(
                type: ! is_null($request->input(key: 'type')) ? RoomTypeEnum::tryFrom($request->input(key: 'type')) : null,
                status: ! is_null($request->input(key: 'status')) ? RoomStatusEnum::tryFrom($request->input(key: 'status')) : null,
                code: $request->input(key: 'code') ?? null,
                codeQuiz: $request->input(key: 'code_quiz') ?? null,
                startTime: $request->input(key: 'start_time') ? Carbon::parse($request->input(key: 'start_time')) : null,
                endTime: $request->input(key: 'end_time') ? Carbon::parse($request->input(key: 'end_time')) : now(),
                page: $request->input(key: 'page', default: self::DEFAULT_PAGE),
            );
            $listRoom = $this->roomService->getListRoomReport(listRoomReportParam: $paramFilter);

            return $this->respondWithJson(content: $listRoom->toArray());
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

    public function deleteReport(string $roomId): JsonResponse
    {
        try {
            $this->roomService->deleteReport(roomId: $roomId);

            return $this->respondWithJson(content: []);
        } catch (Throwable $e) {
            return $this->respondWithJsonError(e: $e);
        }
    }
}
