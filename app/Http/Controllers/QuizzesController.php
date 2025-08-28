<?php

namespace App\Http\Controllers;

use App\DTOs\Answer\CreateAnswerDTO;
use App\DTOs\Question\CreateQuestionDTO;
use App\DTOs\Quizz\CreateQuizDTO;
use App\DTOs\Quizz\SearchQuizDTO;
use App\Enums\Quiz\CreatedByEnum;
use App\Enums\Quiz\TypeQuizEnum;
use App\Enums\User\UserRoleEnum;
use App\Http\Requests\ListQuizzesRequest;
use App\Http\Requests\SearchQuizzeRequest;
use App\Http\Requests\ShareQuizRequest;
use App\Http\Requests\AdminCreateQuizzeRequest;
use App\Http\Requests\ShareQuestionRequest;
use App\Models\User;
use App\Services\Interface\QuizzesServiceInterface;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class QuizzesController extends Controller
{
    public function __construct(
        private readonly QuizzesServiceInterface $quizzesService,
    ) {}

    public function listQuizzesPagination(ListQuizzesRequest $request): JsonResponse
    {
        try {
            $listQuizzes = $this->quizzesService->listQuizzes(
                type: $request->input(key: 'type') ?
                    TypeQuizEnum::from($request->input(key: 'type')) : TypeQuizEnum::ALL
            );

            return $this->respondWithJson(content: $listQuizzes->toArray());
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function createQuiz(AdminCreateQuizzeRequest $request): JsonResponse
    {
        $authUser = Auth::user();
        /** @var User $authUser */
        try {
            $quiz = new CreateQuizDTO(
                title: $request->input(key: 'quizze')['title'],
                categoryId: $request->input(key: 'quizze')['category_id'],
                userId: Auth::id(),
                createdBySys: $authUser->type == UserRoleEnum::SYSTEM->value,
            );
            $questions = [];
            foreach ($request->input(key: 'questions') as $question) {
                $answers = [];
                foreach ($question['answers'] as $answer) {
                    $answers[] = new CreateAnswerDTO(
                        answer: $answer['answer'],
                        isCorrect: $answer['is_correct'],
                    );
                }
                $questions[] = new CreateQuestionDTO(
                    title: $question['title'],
                    answers: $answers,
                    image: $question['image'] ?? null,
                    timeReply: !empty($question['time_reply']) ? $question['time_reply'] : config('app.quizzes.time_reply')
                );
            }
            $this->quizzesService->createQuiz(quizDTO: $quiz, questionDTO: $questions);

            return $this->respondWithJson(content: []);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function deleteQuiz(string $quizId): JsonResponse
    {
        try {
            $this->quizzesService->deleteQuiz(quizId: $quizId);

            return $this->respondWithJson(content: []);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function listQuestionOfQuiz(string $quizId): JsonResponse
    {
        try {
            $listQuestion = $this->quizzesService->listQuestionOfQuiz(quizId: $quizId);

            return $this->respondWithJson(content: $listQuestion->toArray());
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function shareQuiz(string $quizId, ShareQuestionRequest $request): JsonResponse
    {
        try {
            $this->quizzesService->shareQuiz(quizId: $quizId, email: $request->input(key: 'email'));

            return $this->respondWithJson(content: []);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function acceptShareQuiz(string $token, ShareQuizRequest $request): JsonResponse
    {
        try {
            $this->quizzesService->acceptShareQuiz(token: $token, notifyId: $request->input(key: 'notification_id'));

            return $this->respondWithJson(content: []);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function detailShareQuiz(string $token, ShareQuizRequest $request): JsonResponse
    {
        try {
            $userShare = $this->quizzesService->detailShareQuiz(token: $token, notifyId: $request->input(key: 'notification_id'));

            return $this->respondWithJson(content: $userShare->toArray());
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function rejectShareQuiz(string $token, ShareQuizRequest $request): JsonResponse
    {
        try {
            $this->quizzesService->rejectShareQuiz(token: $token, notifyId: $request->input(key: 'notification_id'));
            return $this->respondWithJson(content: []);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function allQuizzesPagination(SearchQuizzeRequest $request): JsonResponse
    {
        try {
            $searchQuizDTO = new SearchQuizDTO(
                userIds: $request->input(key: 'user_ids'),
                code: $request->input(key: 'code'),
                createdAt: !empty($request->input('start_time')) && !empty($request->input('end_time')) ?
                    [$request->input(key: 'start_time'), $request->input(key: 'end_time')] : [],
                categoryId: $request->input(key: 'category_id'),
                createdBy: !empty($request->input(key: 'created_by')) ?
                    CreatedByEnum::tryFrom($request->input(key: 'created_by')) : null,
            );
            $listQuizzes = $this->quizzesService->searchQuiz(searchQuizDTO: $searchQuizDTO);

            return $this->respondWithJson(content: $listQuizzes->toArray());
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function findByKeyword(?string $keyword = null): JsonResponse
    {
        try {
            $quizzes = $this->quizzesService->findByKeyword(keyword: $keyword);

            return $this->respondWithJson(content: $quizzes->toArray());
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }
}
