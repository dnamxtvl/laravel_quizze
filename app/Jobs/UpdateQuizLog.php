<?php

namespace App\Jobs;

use App\Repository\Interface\QuizzesRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateQuizLog implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $quizId,
        private readonly ?string $oldQuestionId = null,
        private readonly ?string $newQuestionId = null,
    ) {
    }

    /**
     * Execute the job.
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        $quizRepository = app()->make(QuizzesRepositoryInterface::class);
        $quizRepository->updateQuizHistory(quizId: $this->quizId, oldQuestionId: $this->oldQuestionId, newQuestionId: $this->newQuestionId);
    }
}
