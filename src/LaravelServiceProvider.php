<?php

namespace Pbmedia\ApiHealth;

use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/api-health.php' => config_path('api-health.php'),
            ], 'config');

            /*
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'api-health');

        $this->publishes([
        __DIR__.'/../resources/views' => base_path('resources/views/vendor/api-health'),
        ], 'views');
         */
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/api-health.php', 'api-health');
    }
}