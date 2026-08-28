<?php

namespace App\Policies;

use App\Models\Campus;
use App\Models\User;

class CampusPolicy
{
    public function enterDashboard(User $user, Campus $campus): bool
    {
        return $user->hasRole('Super Admin') && $campus->is_active;
    }
}
