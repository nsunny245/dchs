<?php

namespace App\Policies;

use App\Models\Admission;
use App\Models\User;

class AdmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'Super Admin', 'Campus Principal', 'Admission Officer', 'Finance',
            'Franchisor Inbound', 'Franchisor Outbound',
        ]);
    }

    public function view(User $user, Admission $admission): bool
    {
        return $user->hasRole('Super Admin')
            || $user->campus_id === $admission->campus_id
            || ($user->franchisor && $user->franchisor->id === $admission->franchisor_id)
            || $user->hasAnyRole(['Franchisor Inbound', 'Franchisor Outbound']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'Super Admin', 'Campus Principal', 'Admission Officer',
            'Franchisor Inbound', 'Franchisor Outbound',
        ]);
    }

    public function update(User $user, Admission $admission): bool
    {
        return $this->create($user) && $this->view($user, $admission);
    }

    public function delete(User $user, Admission $admission): bool
    {
        return $user->hasRole('Super Admin');
    }
}
