<?php

namespace App\Http\Middleware;

use App\Models\DiningTable;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('qr.*')) {
            $table = DiningTable::query()
                ->where('qr_token', $request->route('token'))
                ->with('branch.organization.currentSubscription')
                ->first();

            $organization = $table?->branch?->organization;
            if ($table && (
                ! $organization
                || $organization->status !== 'active'
                || ! $organization->currentSubscription?->grantsAccess()
            )) {
                abort(403, 'This restaurant is not currently accepting online orders.');
            }

            return $next($request);
        }

        $user = $request->user();
        if (! $user
            || $user->isPlatformAdmin()
            || ! $user->organization_id
            || $request->routeIs('admin.billing.*', 'admin.logout')) {
            return $next($request);
        }

        $organization = $user->organization;
        if (! $organization || $organization->status !== 'active') {
            return redirect()->route('admin.billing.index')
                ->with('error', 'This restaurant workspace is inactive. Contact the platform administrator.');
        }

        if (! $organization->currentSubscription?->grantsAccess()) {
            return redirect()->route('admin.billing.index')
                ->with('error', 'Your trial or subscription has ended. Choose a plan to restore workspace access.');
        }

        return $next($request);
    }
}
