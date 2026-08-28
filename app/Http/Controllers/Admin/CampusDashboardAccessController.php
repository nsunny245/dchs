<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Services\Campus\CampusDashboardAccessService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CampusDashboardAccessController extends Controller
{
    public function enter(
        Request $request,
        Campus $campus,
        CampusDashboardAccessService $accessService,
    ): RedirectResponse {
        Gate::forUser(Auth::guard('admin')->user())->authorize('enterDashboard', $campus);

        try {
            $accessService->enter(Auth::guard('admin')->user(), $campus, $request);
        } catch (DomainException $exception) {
            return back()->with('campus_access_error', $exception->getMessage());
        }

        return redirect()
            ->route('filament.campus.pages.dashboard')
            ->with('campus_access_success', "You are now viewing {$campus->name}.");
    }

    public function exit(Request $request, CampusDashboardAccessService $accessService): RedirectResponse
    {
        $accessService->exit($request);

        if (! Auth::guard('admin')->check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        return redirect()
            ->route('filament.admin.pages.dashboard')
            ->with('campus_access_success', 'Returned to the Super Admin dashboard.');
    }
}
