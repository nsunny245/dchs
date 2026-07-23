<?php

namespace App\Policies;

use App\Models\Concession;
use App\Models\User;

class ConcessionPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Admission Officer']);
    }

    public function approve(User $user, Concession $concession): bool
    {
        return $user->hasRole('Super Admin') && $concession->requested_by !== $user->id;
    }
}
