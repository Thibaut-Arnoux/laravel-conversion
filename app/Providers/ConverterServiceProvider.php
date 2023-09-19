<?php

namespace App\Providers;

use App\Converter\ConverterFactory;
use App\Converter\IConverterFactory;
use Illuminate\Support\ServiceProvider;

class ConverterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(IConverterFactory::class, function () {
            return new ConverterFactory();
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
