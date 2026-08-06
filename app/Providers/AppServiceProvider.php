<?php

namespace App\Providers;

use App\Ai\Support\InterceptLogRecorder;
use App\Ai\Support\PromptInspector;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(PromptInspector::class);
        $this->app->scoped(InterceptLogRecorder::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Log::listen(fn (MessageLogged $event) => resolve(InterceptLogRecorder::class)->capture($event));
    }
}
