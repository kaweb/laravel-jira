<?php

namespace Kaweb\Jira;

use Illuminate\Support\ServiceProvider;

class JiraServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-jira.php', 'laravel-jira');

        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Kaweb\Jira\Exceptions\Handler::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-jira.php' => config_path('laravel-jira.php'),
        ], 'config');
    }
}
