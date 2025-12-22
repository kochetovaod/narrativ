<?php

namespace App\Providers;

use App\Auth\ActiveUserProvider;
use App\Observers\MediaObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
        Auth::provider('eloquent_active', function ($app, array $config) {
            return new ActiveUserProvider($app['hash'], $config['model']);
        });

        Media::observe(MediaObserver::class);
    }
}
