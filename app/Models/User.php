<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->email === 'admin@admin.com' || $this->hasRole('Super Admin');
        }

        if ($panel->getId() === 'campus') {
            return $this->campus_id !== null || $this->hasAnyRole(['Campus Principal', 'Admission Officer', 'Faculty', 'Finance']) || $this->hasRole('Super Admin');
        }

        return true;
    }

    public function hasRole($roles, $guard = null): bool
    {
        return parent::hasRole($roles, $guard ?? 'web');
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        return parent::hasPermissionTo($permission, $guardName ?? 'web');
    }

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'campus_id', 'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }
}
