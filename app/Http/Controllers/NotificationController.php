<?php

namespace App\Http\Controllers;

use App\Services\Interface\NotificationServiceInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class NotificationController extends Controller
{
    CONST DEFAULT_SKIP = 5;
    public function __construct(
        private readonly NotificationServiceInterface $notificationService
    ) {}

    public function listNotify(Request $request): JsonResponse
    {
        try {
            $listNotify = $this->notificationService->listNotify(latestNotifyId: $request->input(key: 'latest_notify_id') ?? null);

            return $this->respondWithJson(content: $listNotify->toArray());
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }
}
