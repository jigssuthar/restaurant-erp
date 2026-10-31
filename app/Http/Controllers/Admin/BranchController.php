<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    use ScopesBranchData;

    public function index(Request $request)
    {
        $branches = $this->branchQuery()
            ->with('organization')
            ->withCount(['users', 'categories', 'products', 'diningTables'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('branch_code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereHas('organization', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('organization_id'), function ($query) use ($request) {
                $query->where('organization_id', $request->input('organization_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $organizations = $this->organizationOptions();

        return view('admin.branches.index', compact('branches', 'organizations'));
    }

    public function create()
    {
        $organizations = $this->organizationOptions();

        return view('admin.branches.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        Branch::create($this->validatedData($request));

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        $this->ensureBranchAccess($branch);
        $branch->load('organization')
            ->loadCount(['users', 'categories', 'products', 'diningTables']);

        return view('admin.branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        $this->ensureBranchAccess($branch);
        $organizations = $this->organizationOptions();

        return view('admin.branches.edit', compact('branch', 'organizations'));
    }

    public function update(Request $request, Branch $branch)
    {
        $this->ensureBranchAccess($branch);
        $branch->update($this->validatedData($request, $branch));

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $this->ensureBranchAccess($branch);

        if ($branch->users()->exists()
            || $branch->categories()->exists()
            || $branch->products()->exists()
            || $branch->diningTables()->exists()
            || $branch->orders()->exists()) {
            return back()->with(
                'error',
                'Branch cannot be deleted while it has staff, categories, products, dining tables, or orders.'
            );
        }

        $branch->delete();

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Branch deleted successfully.');
    }

    private function validatedData(Request $request, ?Branch $branch = null): array
    {
        $request->merge([
            'branch_code' => strtoupper(trim((string) $request->input('branch_code'))),
        ]);

        $organizationRule = auth()->user()->isPlatformAdmin()
            ? Rule::exists('organizations', 'id')
            : Rule::in([auth()->user()->organization_id]);

        return $request->validate([
            'organization_id' => ['required', 'integer', $organizationRule],
            'name' => ['required', 'string', 'max:255'],
            'branch_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('branches', 'branch_code')
                    ->where('organization_id', $request->input('organization_id'))
                    ->ignore($branch),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function organizationOptions()
    {
        $query = Organization::query();

        if (! auth()->user()->isPlatformAdmin()) {
            $query->whereKey(auth()->user()->organization_id ?? 0);
        }

        return $query->orderBy('name')->get(['id', 'name']);
    }
}
