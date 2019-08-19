<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Templater;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Templater::class, function ($app) {
            return new Templater(config('factures.template'));
        });
    }
}
