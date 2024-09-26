<?php

namespace App\Services\Implement;

use App\DTOs\Question\CreateQuestionDTO;
use App\Models\Question;
use App\Repository\Interface\QuestionRepositoryInterface;
use App\Repository\Interface\QuizzesRepositoryInterface;
use App\Services\Interface\QuestionServiceInterface;
use Illuminate\Support\Facades\DB;
use Log;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

readonly class QuestionService implements QuestionServiceInterface
{
    public function __construct(
        private QuestionRepositoryInterface $questionRepository,
        private QuizzesRepositoryInterface $quizzesRepository,
    ) {}

    /**
     * @throws InternalErrorException
     */
    public function updateQuestion(string $questionId, CreateQuestionDTO $questionDTO): void
    {
        $question = $this->questionRepository->findById(questionId: $questionId);
        if (is_null($question) || $question->is_old_question) {
            throw new NotFoundHttpException(message: 'Không tìm thấy câu hỏi!');
        }

        DB::beginTransaction();
        try {
            $questionDTO->setQuizId(quizId: $question->quizze_id);
            $this->questionRepository->createQuestion(questionDTO: $questionDTO, indexQuestionOverride: $question->index_question);
            $this->questionRepository->setIsOldQuestion(question: $question, isOldQuestion: true);

            DB::commit();
        } catch (Throwable $th) {
            Log::error(message: $th->getMessage());
            DB::rollBack();
            throw new InternalErrorException(message: 'Có lỗi xảy ra!');
        }
    }

    /**
     * @throws InternalErrorException
     */
    public function addQuestion(string $quizId, CreateQuestionDTO $questionDTO): Question
    {
        $quiz = $this->quizzesRepository->findById(quizId: $quizId);
        if (is_null($quiz)) {
            throw new NotFoundHttpException(message: 'Không tìm thấy bộ câu hỏi!');
        }

        $questions = $this->questionRepository->listQuestionOfQuiz(quizId: $quizId);
        $questionIndex = $questions[$questions->count() - 1]->index_question + 1;
        DB::beginTransaction();
        try {
            $questionDTO->setQuizId(quizId: $quizId);
            $newQuestion = $this->questionRepository->createQuestion(questionDTO: $questionDTO, indexQuestionOverride: $questionIndex);
            DB::commit();
            return $newQuestion;
        } catch (Throwable $th) {
            Log::error(message: $th->getMessage());
            DB::rollBack();
            throw new InternalErrorException(message: 'Có lỗi xảy ra!');
        }
    }

    /**
     * @throws InternalErrorException
     */
    public function deleteQuestion(string $questionId): void
    {
        $question = $this->questionRepository->findById(questionId: $questionId);
        if (is_null($question) || $question->is_old_question) {
            throw new NotFoundHttpException(message: 'Không tìm thấy câu hỏi!');
        }

        $listQuestionOfQuiz = $this->questionRepository->listQuestionOfQuiz(quizId: $question->quizze_id);
        if ($listQuestionOfQuiz->count() <= config(key: 'app.quizzes.min_question')) {
            throw new BadRequestHttpException(message: 'Mỗi Quizz phải có tối thiểu 1 câu hỏi!');
        }

        DB::beginTransaction();
        try {
            $this->questionRepository->deleteQuestion(question: $question);
            DB::commit();
        } catch (Throwable $th) {
            Log::error(message: $th->getMessage());
            DB::rollBack();
            throw new InternalErrorException(message: 'Có lỗi xảy ra!');
        }
    }
}
