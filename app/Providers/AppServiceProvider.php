<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $view->with('globalPrograms', \App\Models\Course::where('is_active', true)->orderBy('name')->get());
            $view->with('globalCampuses', \App\Models\Campus::where('is_active', true)->orderBy('city')->get());
        });
    }
}
