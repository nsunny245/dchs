<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (str_contains(base_path(), 'public_html')) {
            $this->app->usePublicPath(realpath(base_path('../')));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Only the public site layout consumes this value. Applying the
        // composer to every nested Filament view repeated these queries many
        // times during each Livewire render.
        View::composer('layouts.app', function ($view) {
            $view->with(
                'globalPrograms',
                Schema::hasTable('courses')
                    ? \App\Models\Course::query()->where('is_active', true)->orderBy('name')->get()
                    : collect(),
            );
        });
    }
}
