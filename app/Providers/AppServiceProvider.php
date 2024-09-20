<?php

namespace App\Providers;

use App\Repository\Implement\AnswerRepository;
use App\Repository\Implement\GamerRepository;
use App\Repository\Implement\GamerTokenRepository;
use App\Repository\Implement\QuestionRepository;
use App\Repository\Implement\QuizzesRepository;
use App\Repository\Implement\RoomRepository;
use App\Repository\Interface\AnswerRepositoryInterface;
use App\Repository\Interface\GamerRepositoryInterface;
use App\Repository\Interface\GamerTokenRepositoryInterface;
use App\Repository\Interface\QuestionRepositoryInterface;
use App\Repository\Interface\QuizzesRepositoryInterface;
use App\Repository\Interface\RoomRepositoryInterface;
use App\Services\Implement\AuthService;
use App\Services\Implement\GamerService;
use App\Services\Implement\QuizzesService;
use App\Services\Implement\RoomService;
use App\Services\Interface\AuthServiceInterface;
use App\Services\Interface\GamerServiceInterface;
use App\Services\Interface\QuizzesServiceInterface;
use App\Services\Interface\RoomServiceInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for(name: 'api', callback: function (Request $request) {
            return Limit::perSecond(maxAttempts: self::PER_SECOND_DEFAULT)->by(key: $request->user()?->id ?: $request->ip());
        });
    }
}
