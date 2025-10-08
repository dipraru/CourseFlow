<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\BackfillUserProfiles;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                BackfillUserProfiles::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
