<?php

namespace App\Policies;

use App\Models\FeeHead;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeeHeadPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability)
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance']);
    }

    public function view(User $user, FeeHead $feeHead): bool
    {
        if ($user->campus_id && $feeHead->campus_id && $feeHead->campus_id !== $user->campus_id) {
            return false;
        }
        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance']);
    }

    public function update(User $user, FeeHead $feeHead): bool
    {
        if ($user->campus_id && $feeHead->campus_id && $feeHead->campus_id !== $user->campus_id) {
            return false;
        }
        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance']);
    }

    public function delete(User $user, FeeHead $feeHead): bool
    {
        if ($user->campus_id && $feeHead->campus_id && $feeHead->campus_id !== $user->campus_id) {
            return false;
        }
        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance']);
    }
}
