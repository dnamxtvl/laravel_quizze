<?php

namespace App\Providers;

use App\Events\EmailNotVerifyEvent;
use App\Listeners\SendEmailVerifyOTPNotification;
use App\Repository\Implement\AnswerRepository;
use App\Repository\Implement\BlockUserLoginTemporaryRepository;
use App\Repository\Implement\CategoryRepository;
use App\Repository\Implement\EmailVerifyOTPRepository;
use App\Repository\Implement\GamerRepository;
use App\Repository\Implement\GamerTokenRepository;
use App\Repository\Implement\NotificationRepository;
use App\Repository\Implement\QuestionRepository;
use App\Repository\Implement\QuizzesRepository;
use App\Repository\Implement\RoomRepository;
use App\Repository\Implement\UserForgotPasswordLogRepository;
use App\Repository\Implement\UserLoginHistoryRepository;
use App\Repository\Implement\UserRepository;
use App\Repository\Implement\UserShareQuizRepository;
use App\Repository\Interface\AnswerRepositoryInterface;
use App\Repository\Interface\BlockUserLoginTemporaryRepositoryInterface;
use App\Repository\Interface\CategoryRepositoryInterface;
use App\Repository\Interface\EmailVerifyOTPRepositoryInterface;
use App\Repository\Interface\GamerRepositoryInterface;
use App\Repository\Interface\GamerTokenRepositoryInterface;
use App\Repository\Interface\NotificationRepositoryInterface;
use App\Repository\Interface\QuestionRepositoryInterface;
use App\Repository\Interface\QuizzesRepositoryInterface;
use App\Repository\Interface\RoomRepositoryInterface;
use App\Repository\Interface\UserForgotPasswordLogRepositoryInterface;
use App\Repository\Interface\UserLoginHistoryRepositoryInterface;
use App\Repository\Interface\UserRepositoryInterface;
use App\Repository\Interface\UserShareQuizRepositoryInterface;
use App\Services\Implement\AuthService;
use App\Services\Implement\GamerService;
use App\Services\Implement\NotificationService;
use App\Services\Implement\QuestionService;
use App\Services\Implement\QuizzesService;
use App\Services\Implement\RoomService;
use App\Services\Implement\UserService;
use App\Services\Interface\AuthServiceInterface;
use App\Services\Interface\GamerServiceInterface;
use App\Services\Interface\NotificationServiceInterface;
use App\Services\Interface\QuestionServiceInterface;
use App\Services\Interface\QuizzesServiceInterface;
use App\Services\Interface\RoomServiceInterface;
use App\Services\Interface\UserServiceInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    const PER_SECOND_DEFAULT = 10;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(abstract: AuthServiceInterface::class, concrete: AuthService::class);
        $this->app->singleton(abstract: QuizzesServiceInterface::class, concrete: QuizzesService::class);
        $this->app->singleton(abstract: QuizzesRepositoryInterface::class, concrete: QuizzesRepository::class);
        $this->app->singleton(abstract: RoomServiceInterface::class, concrete: RoomService::class);
        $this->app->singleton(abstract: RoomRepositoryInterface::class, concrete: RoomRepository::class);
        $this->app->singleton(abstract: GamerRepositoryInterface::class, concrete: GamerRepository::class);
        $this->app->singleton(abstract: GamerTokenRepositoryInterface::class, concrete: GamerTokenRepository::class);
        $this->app->singleton(abstract: GamerServiceInterface::class, concrete: GamerService::class);
        $this->app->singleton(abstract: QuestionRepositoryInterface::class, concrete: QuestionRepository::class);
        $this->app->singleton(abstract: AnswerRepositoryInterface::class, concrete: AnswerRepository::class);
        $this->app->singleton(abstract: QuestionServiceInterface::class, concrete: QuestionService::class);
        $this->app->singleton(abstract: NotificationRepositoryInterface::class, concrete: NotificationRepository::class);
        $this->app->singleton(abstract: UserRepositoryInterface::class, concrete: UserRepository::class);
        $this->app->singleton(abstract: UserShareQuizRepositoryInterface::class, concrete: UserShareQuizRepository::class);
        $this->app->singleton(abstract: NotificationServiceInterface::class, concrete: NotificationService::class);
        $this->app->singleton(abstract: CategoryRepositoryInterface::class, concrete: CategoryRepository::class);
        $this->app->singleton(abstract: UserServiceInterface::class, concrete: UserService::class);
        $this->app->singleton(abstract: BlockUserLoginTemporaryRepositoryInterface::class, concrete: BlockUserLoginTemporaryRepository::class);
        $this->app->singleton(abstract: EmailVerifyOTPRepositoryInterface::class, concrete: EmailVerifyOTPRepository::class);
        $this->app->singleton(abstract: UserForgotPasswordLogRepositoryInterface::class, concrete: UserForgotPasswordLogRepository::class);
        $this->app->singleton(abstract: UserLoginHistoryRepositoryInterface::class, concrete: UserLoginHistoryRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for(name: 'api', callback: function (Request $request) {
            return Limit::perSecond(maxAttempts: self::PER_SECOND_DEFAULT)->by(key: $request->user()?->id ?: $request->ip());
        });

        Event::listen(
            EmailNotVerifyEvent::class,
            SendEmailVerifyOTPNotification::class,
        );
    }
}
