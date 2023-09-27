<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TemporaryDirectory::class, function () {
            return (new TemporaryDirectory())->create();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
