<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;

trait ScopesBranchData
{
    protected function branchQuery(): Builder
    {
        $query = Branch::query();
        $user = request()->user();

        if ($user->isPlatformAdmin()) {
            return $query;
        }

        if ($user->hasAnyRole('admin', 'manager')) {
            return $query->where('organization_id', $user->organization_id ?? 0);
        }

        if ($user->hasAnyRole('staff')) {
            return $query->whereKey($user->branch_id ?? 0);
        }

        return $query->whereRaw('1 = 0');
    }

    protected function scopeToAccessibleBranches(Builder $query, string $column = 'branch_id'): Builder
    {
        return $query->whereIn($column, $this->branchQuery()->select('id'));
    }

    protected function ensureBranchAccess(Branch $branch): void
    {
        abort_unless(
            $this->branchQuery()->whereKey($branch->getKey())->exists(),
            403
        );
    }
}
