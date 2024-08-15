<?php

namespace App\Providers;

use App\Services\Implement\AuthService;
use App\Services\Interface\AuthServiceInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

class AppServiceProvider extends ServiceProvider
{
    const PER_SECOND_DEFAULT = 10;
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(abstract: AuthServiceInterface::class, concrete: AuthService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perSecond(self::PER_SECOND_DEFAULT)->by($request->user()?->id ?: $request->ip());
        });
    }
}
