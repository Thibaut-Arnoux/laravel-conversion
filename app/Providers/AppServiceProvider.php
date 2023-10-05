<?php

namespace App\Providers;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
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
        Model::shouldBeStrict(! app()->isProduction());

        DB::listen(function ($query) {
            File::append(
                storage_path('/logs/query.log'),
                '['.date('Y-m-d H:i:s').']'.PHP_EOL.$query->sql.' ['.implode(', ', $query->bindings).']'.PHP_EOL.PHP_EOL
            );
        });
    }
}
