<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait ScopedByCampus
{
    protected static function booted()
    {
        // Global Scope: Filter all queries dynamically during query builder execution
        static::addGlobalScope('campus', function (Builder $builder) {
            $user = null;
            try {
                if (class_exists(\Filament\Facades\Filament::class) && \Filament\Facades\Filament::getCurrentPanel()) {
                    $user = \Filament\Facades\Filament::auth()->user();
                }
            } catch (\Throwable $e) {}

            if (!$user) {
                foreach (['web', 'campus'] as $guard) {
                    if (auth()->guard($guard)->check()) {
                        $user = auth()->guard($guard)->user();
                        break;
                    }
                }
            }

            if ($user && !$user->hasRole('Super Admin')) {
                $builder->where($builder->getModel()->getTable() . '.campus_id', $user->campus_id);
            }
        });

        // Automatically assign campus_id when creating a new record
        static::creating(function ($model) {
            $user = null;
            try {
                if (class_exists(\Filament\Facades\Filament::class) && \Filament\Facades\Filament::getCurrentPanel()) {
                    $user = \Filament\Facades\Filament::auth()->user();
                }
            } catch (\Throwable $e) {}

            if (!$user) {
                foreach (['web', 'campus'] as $guard) {
                    if (auth()->guard($guard)->check()) {
                        $user = auth()->guard($guard)->user();
                        break;
                    }
                }
            }

            if ($user && !$user->hasRole('Super Admin')) {
                if (empty($model->campus_id)) {
                    $model->campus_id = $user->campus_id;
                }
            }
        });
    }
}
