<?php

namespace App\Providers;

use App\Services\GeminiAIService;
use App\Services\LearningStyleClassifier;
use Illuminate\Support\ServiceProvider;

class LearningStyleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(GeminiAIService::class, function ($app) {
            return new GeminiAIService();
        });

        $this->app->singleton(LearningStyleClassifier::class, function ($app) {
            return new LearningStyleClassifier(
                $app->make(GeminiAIService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
