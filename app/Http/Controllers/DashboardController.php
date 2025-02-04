<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardRequest;
use App\Services\Interface\CategoryServiceInterface;
use App\Services\Interface\GamerServiceInterface;
use App\Services\Interface\QuestionServiceInterface;
use App\Services\Interface\QuizzesServiceInterface;
use App\Services\Interface\RoomServiceInterface;
use App\Services\Interface\UserServiceInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        private readonly CategoryServiceInterface $categoryService,
        private readonly UserServiceInterface $userService,
        private readonly QuestionServiceInterface $questionService,
        private readonly RoomServiceInterface $roomService,
        private readonly GamerServiceInterface $gamerService,
        private readonly QuizzesServiceInterface $quizzesService,
    ) {}

    public function index(DashboardRequest $request): JsonResponse
    {
        $now = now();
        $subYear = (clone $now)->subYear();
        try {
            $currentCategory = $this->categoryService->getCategoryWithCountQuiz(startTime: $now, endTime: $subYear);
            $latestCustomer = $this->userService->getLatestUser();

            $countCustomer = $this->userService->countCustomer();
            $countQuestion = $this->questionService->countQuestion();
            $countRoom = $this->roomService->countRoom();
            $countGamer = $this->gamerService->countGamer();

            $currentGamerYearChart = $request->input(key: 'year_of_gamer_chart') ?? $now->year;
            $startTimeGamerChart = Carbon::create(year: $currentGamerYearChart, month: $now->month, day: $now->day)->endOfDay();
            $endTimeGamerChart = $startTimeGamerChart->copy()->subYear();

            if ((int) $currentGamerYearChart < $now->year) {
                $startTimeGamerChart = Carbon::create(year: $currentGamerYearChart, month: $now->month, day: $now->day)->copy()->endOfYear();
                $endTimeGamerChart = Carbon::create(year: $currentGamerYearChart, month: $now->month, day: $now->day)->copy()->startOfYear();
            }
            $gamerGroupByYear = $this->gamerService->groupByYear(startTime: $startTimeGamerChart, endTime: $endTimeGamerChart)->toArray();
            $gamerGroupByYear = array_column($gamerGroupByYear, 'total', 'month_year');
            $rangeGamerMonthChart = CarbonPeriod::create($endTimeGamerChart, '1 month', $startTimeGamerChart);

            foreach ($rangeGamerMonthChart as $month) {
                $key = $month->format('Y-m');
                $gamerGroupByYear[$key] = $gamerGroupByYear[$key] ?? 0;
            }
            ksort($gamerGroupByYear);

            $previousYearGamerChart = $this->gamerService->countByTime(
                startTime: $startTimeGamerChart->copy()->subYear(),
                endTime: $endTimeGamerChart->copy()->subYear()
            );

            $currentRoomYearChart = $request->input(key: 'year_of_room_chart') ?? $now->year;
            $startTimeRoomChart = Carbon::create(year: $currentRoomYearChart, month: $now->month, day: $now->day)->endOfDay();
            $endTimeRoomChart = $startTimeRoomChart->copy()->subYear();

            if ((int) $currentRoomYearChart < $now->year) {
                $startTimeRoomChart = Carbon::create(year: $currentRoomYearChart, month: $now->month, day: $now->day)->copy()->endOfYear();
                $endTimeRoomChart = Carbon::create(year: $currentRoomYearChart, month: $now->month, day: $now->day)->copy()->startOfYear();
            }

            $roomGroupByYear = $this->roomService->groupByYear(startTime: $startTimeRoomChart, endTime: $endTimeRoomChart)->toArray();
            $roomGroupByYear = array_column($roomGroupByYear, 'total', 'month_year');
            $rangeRoomMonthChart = CarbonPeriod::create($endTimeRoomChart, '1 month', $startTimeRoomChart);

            $previousYearRoomChart = $this->roomService->countByTime(
                startTime: $startTimeRoomChart->copy()->subYear(),
                endTime: $endTimeRoomChart->copy()->subYear()
            );

            foreach ($rangeRoomMonthChart as $month) {
                $key = $month->format('Y-m');
                $roomGroupByYear[$key] = $roomGroupByYear[$key] ?? 0;
            }
            ksort($roomGroupByYear);

            [$totalAnswer, $correctAnswer] = $this->questionService->countAnswerByTime(
                startTime: $now->copy(),
                endTime: $now->copy()->subYear()->startOfDay()
            );

            [$totalQuiz, $totalQuizByUser] = $this->quizzesService->countByTime(
                startTime: $now->copy(),
                endTime: $now->copy()->subMonth()->startOfDay()
            );

            $previousQuiz = $this->quizzesService->countAllByTime(
                startTime: $now->copy()->subMonth()->endOfDay(),
                endTime: $now->copy()->subMonths(2)->startOfDay()
            );

            $totalCurrentShare = $this->quizzesService->totalShareQuiz(
                startTime: $now->copy(),
                endTime: $now->copy()->subMonth()->startOfDay(),
            );

            $totalPreviousMonthShare = $this->quizzesService->totalShareQuiz(
                startTime: $now->copy()->subMonth()->endOfDay(),
                endTime: $now->copy()->subMonths(2)->startOfDay(),
            );

            return $this->respondWithJson(content: [
                'categories' => $currentCategory,
                'latest_customer' => $latestCustomer,
                'overview' => [
                    'count_customer' => $countCustomer,
                    'count_question' => $countQuestion,
                    'count_room' => $countRoom,
                    'count_gamer' => $countGamer,
                ],
                'gamer_chart' => [
                    'gamer_group_by_year' => $gamerGroupByYear,
                    'gamer_chart_label' => array_keys($gamerGroupByYear),
                    'sum_gamer_current_year' => array_sum($gamerGroupByYear),
                    'previous_year' => $previousYearGamerChart,
                    'percent_up' => $previousYearGamerChart ? round((array_sum($gamerGroupByYear) - $previousYearGamerChart) / $previousYearGamerChart * 100) : 100,
                ],
                'room_chart' => [
                    'room_group_by_year' => $roomGroupByYear,
                    'room_chart_label' => array_keys($roomGroupByYear),
                    'sum_room_current_year' => array_sum($roomGroupByYear),
                    'previous_year' => $previousYearRoomChart,
                    'percent_up' => $previousYearRoomChart ? round((array_sum($roomGroupByYear) - $previousYearRoomChart) / $previousYearRoomChart * 100) : 100,
                ],
                'answers' => [
                    'total' => $totalAnswer,
                    'correct' => $correctAnswer,
                    'percent_up' => $correctAnswer ? round((abs($correctAnswer - $totalAnswer)) / $totalAnswer * 100) : 100,
                ],
                'quiz' => [
                    'total' => $totalQuiz,
                    'total_by_user' => $totalQuizByUser,
                    'percent_up' => $previousQuiz ? round((abs($totalQuiz - $previousQuiz)) / $previousQuiz * 100) : 100,
                    'previous_month' => $previousQuiz,
                ],
                'share_quiz' => [
                    'total' => $totalCurrentShare,
                    'previous_month' => $totalPreviousMonthShare,
                    'percent_up' => $totalPreviousMonthShare ? round((abs($totalCurrentShare - $totalPreviousMonthShare)) / $totalPreviousMonthShare * 100) : 100,
                ],
            ]);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }
}
