<?php

namespace App\Http\Controllers;

use App\DTOs\Answer\CreateAnswerDTO;
use App\DTOs\Question\CreateQuestionDTO;
use App\Http\Requests\AdminCreateQuestionRequest;
use App\Http\Requests\AdminUpdateQuestionRequest;
use App\Services\Interface\QuestionServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class QuestionController extends Controller
{
    public function __construct(
        private readonly QuestionServiceInterface $questionService
    ) {}

    public function updateQuestion(string $questionId, AdminUpdateQuestionRequest $request): JsonResponse
    {
        $answers = $request->input(key: 'answers');
        $answersDTOs = [];
        foreach ($answers as $answer) {
            $answersDTOs[] = new CreateAnswerDTO(
                answer: $answer['answer'],
                isCorrect: $answer['is_correct'],
            );
        }
        $question = new CreateQuestionDTO(
            title: $request->input(key: 'title'),
            answers: $answersDTOs,
        );

        try {
            $this->questionService->updateQuestion(questionId: $questionId, questionDTO: $question);
            return $this->respondWithJson(content: []);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function createQuestion(string $quizId, AdminCreateQuestionRequest $request): JsonResponse
    {
        try {
            $answers = $request->input(key: 'answers');
            $answersDTOs = [];
            foreach ($answers as $answer) {
                $answersDTOs[] = new CreateAnswerDTO(
                    answer: $answer['answer'],
                    isCorrect: $answer['is_correct'],
                );
            }
            $question = new CreateQuestionDTO(
                title: $request->input(key: 'title'),
                answers: $answersDTOs,
                quizId: $quizId
            );
            $this->questionService->addQuestion(quizId: $quizId, questionDTO: $question);

            return $this->respondWithJson(content: []);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }

    public function deleteQuestion(string $questionId): JsonResponse
    {
        try {
            $this->questionService->deleteQuestion(questionId: $questionId);
            return $this->respondWithJson(content: []);
        } catch (Throwable $th) {
            return $this->respondWithJsonError(e: $th);
        }
    }
}
