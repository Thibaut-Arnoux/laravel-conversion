<?php

namespace App\Providers;

use App\Converter\ConverterFactory;
use App\Converter\IConverterService;
use App\Models\File;
use Illuminate\Support\ServiceProvider;

class ConverterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(IConverterService::class, function () {
            $file = request()->route('file');

            if ($file instanceof File) {
                // extract file or uuid from request
                return ConverterFactory::createConverter($file->extension);
            }

            abort(404, 'Failed to inject converter');
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
