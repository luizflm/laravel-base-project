<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{DB, Http, URL};
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bootModels();
        $this->bootUrl();
        $this->bootTests();
        $this->bootCommands();
    }

    private function bootModels(): void
    {
        Model::unguard();
        Model::shouldBeStrict(
            !$this->app->isProduction()
        );
    }

    private function bootUrl(): void
    {
        URL::forceHttps($this->app->isProduction());
    }

    private function bootTests(): void
    {
        Http::preventingStrayRequests(!$this->app->isProduction());
    }

    private function bootCommands(): void
    {
        DB::prohibitDestructiveCommands($this->app->isProduction());
    }
}
