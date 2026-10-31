<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\DiningTable;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePlanLimit
{
    public function handle(Request $request, Closure $next, string $resource): Response
    {
        if ($request->route()->getActionMethod() !== 'store') {
            return $next($request);
        }

        $user = $request->user();
        $organization = $user?->organization;
        if (! $organization || $user->isPlatformAdmin()) {
            return $next($request);
        }

        $subscription = $organization->currentSubscription;
        $plan = $subscription?->plan;
        $limit = match ($resource) {
            'branches' => $plan?->max_branches,
            'staff' => $plan?->max_staff,
            'products' => $plan?->max_products,
            'tables' => $plan?->max_tables,
            default => abort(500, 'Unknown plan limit.'),
        };

        if ($limit === null) {
            return $next($request);
        }

        $branchIds = $organization->branches()->select('id');
        $currentCount = match ($resource) {
            'branches' => Branch::where('organization_id', $organization->id)->count(),
            'staff' => $organization->users()->where('role', '!=', 'admin')->count(),
            'products' => Product::whereIn('branch_id', $branchIds)->count(),
            'tables' => DiningTable::whereIn('branch_id', $branchIds)->count(),
        };

        if ($currentCount >= $limit) {
            return back()->with(
                'error',
                "Your {$plan->name} plan allows up to {$limit} {$resource}. Upgrade your plan or remove an existing item."
            );
        }

        return $next($request);
    }
}
