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
    use HasFactory, Notifiable;
    use HasRoles {
        hasRole as traitHasRole;
        hasPermissionTo as traitHasPermissionTo;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->email === 'admin@admin.com' || $this->hasRole('Super Admin');
        }

        if ($panel->getId() === 'campus') {
            $hasCampusRole = false;
            foreach (['Campus Principal', 'Admission Officer', 'Faculty', 'Finance'] as $role) {
                if ($this->hasRole($role)) {
                    $hasCampusRole = true;
                    break;
                }
            }
            return $this->campus_id !== null || $hasCampusRole || $this->hasRole('Super Admin');
        }

        if ($panel->getId() === 'franchisor') {
            $inboundUserId = \App\Models\Setting::where('key', 'franchisor_inbound_user_id')->value('value');
            $outboundUserId = \App\Models\Setting::where('key', 'franchisor_outbound_user_id')->value('value');

            return $this->id == $inboundUserId || $this->id == $outboundUserId || $this->hasRole('Super Admin') || $this->hasRole('Franchisor Inbound') || $this->hasRole('Franchisor Outbound');
        }

        return true;
    }

    public function hasRole($roles, $guard = null): bool
    {
        return $this->traitHasRole($roles, $guard ?? 'web');
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        return $this->traitHasPermissionTo($permission, $guardName ?? 'web');
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
