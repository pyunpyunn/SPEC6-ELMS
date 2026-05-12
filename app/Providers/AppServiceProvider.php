<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Observers\UserObserver;

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
        // Register UserObserver to auto-create Employee profile
        User::observe(UserObserver::class);

        // This tells Fortify: "When someone visits /login, show this Blade file"
        \Laravel\Fortify\Fortify::loginView(function () {
            return view('auth.login'); 
        });
    
        // Optional: Do the same for register if you need it
        \Laravel\Fortify\Fortify::registerView(function () {
            return view('auth.register');
        });
    }
}
