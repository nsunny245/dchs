<?php

namespace App\Policies;

use App\Models\FeeStructure;
use App\Models\User;

class FeeStructurePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance', 'Admission Officer']);
    }

    public function view(User $user, FeeStructure $feeStructure): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function update(User $user, FeeStructure $feeStructure): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function delete(User $user, FeeStructure $feeStructure): bool
    {
        return false;
    }
}
