<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campus extends Model
{
    protected $fillable = ['name', 'code', 'city', 'address', 'phone', 'email', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function principals(): HasMany
    {
        return $this->hasMany(User::class)
            ->where('status', true)
            ->whereHas('roles', fn ($query) => $query
                ->where('name', 'Campus Principal')
                ->where('guard_name', 'web'))
            ->orderBy('id');
    }
}
