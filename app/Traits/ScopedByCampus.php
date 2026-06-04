<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait ScopedByCampus
{
    protected static function booted()
    {
        // Global Scope: Filter all queries if user is NOT a Super Admin
        if (Auth::check() && !Auth::user()->hasRole('Super Admin')) {
            static::addGlobalScope('campus', function (Builder $builder) {
                $builder->where('campus_id', Auth::user()->campus_id);
            });
        }

        // Automatically assign campus_id when creating a new record
        static::creating(function ($model) {
            if (Auth::check() && !Auth::user()->hasRole('Super Admin')) {
                if (empty($model->campus_id)) {
                    $model->campus_id = Auth::user()->campus_id;
                }
            }
        });
    }
}
