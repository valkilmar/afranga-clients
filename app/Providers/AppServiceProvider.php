<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Due to https://github.com/laravel/framework/issues/17508
        Schema::defaultStringLength(191);


        Storage::disk('local')->buildTemporaryUrlsUsing(function ($path, $expiration, $options) {
            list($exportId, $fileName) = explode('/', $path);
            return URL::temporarySignedRoute(
                'client-export-download',
                $expiration,
                array_merge($options, ['exportId' => $exportId, 'path' => $fileName])
            );
        });
    }
}
