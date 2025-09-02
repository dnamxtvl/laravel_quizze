<?php

namespace App\Services\Implement;

use App\DTOs\Question\CreateQuestionDTO;
use App\Jobs\UpdateQuizLog;
use App\Models\Question;
use App\Repository\Interface\AnswerRepositoryInterface;
use App\Repository\Interface\QuestionRepositoryInterface;
use App\Repository\Interface\QuizzesRepositoryInterface;
use App\Services\Interface\QuestionServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

readonly class QuestionService implements QuestionServiceInterface
{
    public function __construct(
        private QuestionRepositoryInterface $questionRepository,
        private QuizzesRepositoryInterface $quizzesRepository,
        private AnswerRepositoryInterface $answerRepository,
    ) {}

    /**
     * @throws InternalErrorException|Throwable
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
            $newQuestion = $this->questionRepository->createQuestion(questionDTO: $questionDTO, indexQuestionOverride: $question->index_question);
            $this->questionRepository->setIsOldQuestion(question: $question, isOldQuestion: true);
            UpdateQuizLog::dispatch($question->quizze_id, $questionId, $newQuestion->id, Auth::id());

            DB::commit();
        } catch (Throwable $th) {
            Log::error(message: $th->getMessage());
            DB::rollBack();
            throw new InternalErrorException(message: 'Có lỗi xảy ra!');
        }
    }

    /**
     * @throws InternalErrorException|Throwable
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
            UpdateQuizLog::dispatch($quizId, null, $newQuestion->id);
            DB::commit();

            return $newQuestion;
        } catch (Throwable $th) {
            Log::error(message: $th->getMessage());
            DB::rollBack();
            throw new InternalErrorException(message: 'Có lỗi xảy ra!');
        }
    }

    /**
     * @throws InternalErrorException|Throwable
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
            UpdateQuizLog::dispatch($question->quizze_id, $questionId, null);

            DB::commit();
        } catch (Throwable $th) {
            Log::error(message: $th->getMessage());
            DB::rollBack();
            throw new InternalErrorException(message: 'Có lỗi xảy ra!');
        }
    }

    public function countQuestion(): int
    {
        return $this->questionRepository->countQuestion();
    }

    public function countAnswerByTime(Carbon $startTime, Carbon $endTime): array
    {
        return $this->answerRepository->countAnswerByTime(startTime: $startTime, endTime: $endTime);
    }
}
