<?php

namespace App\Policies;

use App\Models\FeeVoucher;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeeVoucherPolicy
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
        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance', 'Admission Officer']);
    }

    public function view(User $user, FeeVoucher $voucher): bool
    {
        // Campus scoping
        if ($user->campus_id && $voucher->campus_id !== $user->campus_id) {
            return false;
        }

        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance', 'Admission Officer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance', 'Admission Officer']);
    }

    public function update(User $user, FeeVoucher $voucher): bool
    {
        // Super Admin can edit any voucher
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Campus scoping: Campus Admin can edit vouchers belonging to their campus
        if ($user->campus_id && $voucher->campus_id !== $user->campus_id) {
            return false;
        }

        // Campus Admins (Campus Principal, Finance, Admission Officer) can edit all fee vouchers directly
        return $user->hasAnyRole(['Campus Principal', 'Finance', 'Admission Officer']);
    }

    public function delete(User $user, FeeVoucher $voucher): bool
    {
        // Only draft vouchers can be deleted
        if ($voucher->status !== 'draft') {
            return false;
        }

        // Campus scoping
        if ($user->campus_id && $voucher->campus_id !== $user->campus_id) {
            return false;
        }

        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance']);
    }

    public function issue(User $user, FeeVoucher $voucher): bool
    {
        if ($voucher->status !== 'draft') {
            return false;
        }

        if ($user->campus_id && $voucher->campus_id !== $user->campus_id) {
            return false;
        }

        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance']);
    }

    public function print(User $user, FeeVoucher $voucher): bool
    {
        if ($user->campus_id && $voucher->campus_id !== $user->campus_id) {
            return false;
        }

        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance', 'Admission Officer']);
    }

    public function download(User $user, FeeVoucher $voucher): bool
    {
        if ($user->campus_id && $voucher->campus_id !== $user->campus_id) {
            return false;
        }

        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance', 'Admission Officer']);
    }

    public function recordPayment(User $user, FeeVoucher $voucher): bool
    {
        if (in_array($voucher->status, ['paid', 'cancelled', 'void'])) {
            return false;
        }

        if ($user->campus_id && $voucher->campus_id !== $user->campus_id) {
            return false;
        }

        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance']);
    }

    public function cancel(User $user, FeeVoucher $voucher): bool
    {
        // Cannot cancel paid vouchers
        if ($voucher->status === 'paid') {
            return false;
        }

        if ($user->campus_id && $voucher->campus_id !== $user->campus_id) {
            return false;
        }

        return $user->hasAnyRole(['Super Admin', 'Campus Principal', 'Finance']);
    }

    public function void(User $user, FeeVoucher $voucher): bool
    {
        if ($voucher->status === 'paid') {
            return false;
        }

        if ($user->campus_id && $voucher->campus_id !== $user->campus_id) {
            return false;
        }

        return $user->hasAnyRole(['Super Admin']);
    }
}
