<?php

namespace App\Services\Campus;

use App\Models\Campus;
use App\Models\CampusDashboardAccessLog;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampusDashboardAccessService
{
    public const SESSION_KEY = 'super_admin_campus_access';

    public function enter(User $superAdmin, Campus $campus, Request $request): CampusDashboardAccessLog
    {
        if (! $superAdmin->hasRole('Super Admin') || ! $campus->is_active) {
            throw new AuthorizationException('You are not authorized to open this campus dashboard.');
        }

        $campusUser = $campus->principals()->first();

        if (! $campusUser) {
            throw new DomainException('Assign an active Campus Principal before opening this campus dashboard.');
        }

        $this->closeExistingAccess($request);

        Auth::guard('campus')->login($campusUser);
        $request->session()->regenerate();

        $log = CampusDashboardAccessLog::create([
            'super_admin_user_id' => $superAdmin->id,
            'campus_id' => $campus->id,
            'campus_user_id' => $campusUser->id,
            'session_id' => $request->session()->getId(),
            'entered_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->session()->put(self::SESSION_KEY, [
            'log_id' => $log->id,
            'super_admin_user_id' => $superAdmin->id,
            'campus_id' => $campus->id,
            'campus_user_id' => $campusUser->id,
        ]);

        return $log->load(['campus', 'campusUser', 'superAdmin']);
    }

    public function exit(Request $request): void
    {
        $access = $request->session()->get(self::SESSION_KEY, []);

        if (! empty($access['log_id'])) {
            CampusDashboardAccessLog::query()
                ->whereKey($access['log_id'])
                ->whereNull('exited_at')
                ->update(['exited_at' => now()]);
        }

        Auth::guard('campus')->logout();
        $request->session()->forget(self::SESSION_KEY);
        $request->session()->regenerate();
        $request->session()->regenerateToken();
    }

    public function currentAccess(Request $request): ?CampusDashboardAccessLog
    {
        $access = $request->session()->get(self::SESSION_KEY, []);
        $superAdmin = Auth::guard('admin')->user();
        $campusUser = Auth::guard('campus')->user();

        if (
            ! $superAdmin
            || ! $superAdmin->hasRole('Super Admin')
            || ! $campusUser
            || empty($access['log_id'])
            || (int) ($access['super_admin_user_id'] ?? 0) !== $superAdmin->id
            || (int) ($access['campus_user_id'] ?? 0) !== $campusUser->id
        ) {
            return null;
        }

        return CampusDashboardAccessLog::query()
            ->with(['campus', 'campusUser', 'superAdmin'])
            ->whereKey($access['log_id'])
            ->where('super_admin_user_id', $superAdmin->id)
            ->where('campus_user_id', $campusUser->id)
            ->where('campus_id', $access['campus_id'] ?? 0)
            ->whereNull('exited_at')
            ->first();
    }

    private function closeExistingAccess(Request $request): void
    {
        $access = $request->session()->get(self::SESSION_KEY, []);

        if (! empty($access['log_id'])) {
            CampusDashboardAccessLog::query()
                ->whereKey($access['log_id'])
                ->whereNull('exited_at')
                ->update(['exited_at' => now()]);
        }

        if (Auth::guard('campus')->check()) {
            Auth::guard('campus')->logout();
        }

        $request->session()->forget(self::SESSION_KEY);
    }
}
