<?php

namespace App\Http\Controllers;

use App\DTOs\Answer\CreateAnswerDTO;
use App\DTOs\Question\CreateQuestionDTO;
use App\DTOs\Quizz\CreateQuizzDTO;
use App\Http\Requests\AdminCreateQuizzeRequest;
use App\Http\Requests\ShareQuestionRequest;
use App\Services\Interface\QuizzesServiceInterface;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class QuizzesController extends Controller
{
    public function __construct(
        private readonly QuizzesServiceInterface $quizzesService,
    ) {}

    public function listQuizzesPagination(): JsonResponse
    {
        try {
            $listQuizzes = $this->quizzesService->listQuizzes();

            return $this->respondWithJson(content: $listQuizzes->toArray());
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function createQuiz(AdminCreateQuizzeRequest $request): JsonResponse
    {
        try {
            $quiz = new CreateQuizzDTO(
                title: $request->input(key: 'quizze')['title'],
                categoryId: $request->input(key: 'quizze')['category_id'],
                userId: Auth::id(),
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
                    answers: $answers
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
}
