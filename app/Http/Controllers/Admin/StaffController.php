<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $staff = $this->staffQuery()->with(['organization', 'branch'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role', $request->input('role'));
            })
            ->when($request->filled('organization_id') && auth()->user()->isPlatformAdmin(), function ($query) use ($request) {
                $query->where('organization_id', $request->input('organization_id'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $organizations = $this->organizationQuery()->orderBy('name')->get(['id', 'name']);

        $roles = User::ROLES;

        return view('admin.staff.index', compact('staff', 'organizations', 'roles'));
    }

    public function create()
    {
        return view('admin.staff.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        User::create($this->validatedData($request));

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff account created successfully.');
    }

    public function show(User $staff)
    {
        $this->ensureStaffAccess($staff);
        $staff->load(['organization', 'branch']);

        return view('admin.staff.show', compact('staff'));
    }

    public function edit(User $staff)
    {
        $this->ensureStaffAccess($staff);

        return view('admin.staff.edit', array_merge(
            ['staff' => $staff],
            $this->formOptions()
        ));
    }

    public function update(Request $request, User $staff)
    {
        $this->ensureStaffAccess($staff);
        $data = $this->validatedData($request, $staff);

        if ($staff->is(auth()->user())
            && $data['role'] !== $staff->role) {
            return back()
                ->withInput()
                ->withErrors(['role' => 'You cannot change your own role.']);
        }

        if ($staff->isPlatformAdmin()
            && $data['role'] !== 'admin'
            && User::where('role', 'admin')->whereNull('organization_id')->count() <= 1) {
            return back()
                ->withInput()
                ->withErrors(['role' => 'The last administrator cannot be demoted.']);
        }

        $staff->update($data);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff account updated successfully.');
    }

    public function destroy(User $staff)
    {
        $this->ensureStaffAccess($staff);

        if ($staff->is(auth()->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($staff->isPlatformAdmin()
            && User::where('role', 'admin')->whereNull('organization_id')->count() <= 1) {
            return back()->with('error', 'The last administrator cannot be deleted.');
        }

        $staff->delete();

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff account deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'organizations' => $this->organizationQuery()->orderBy('name')->get(['id', 'name']),
            'branches' => Branch::with('organization')
                ->when(! auth()->user()->isPlatformAdmin(), fn ($query) => $query->where('organization_id', auth()->user()->organization_id ?? 0))
                ->orderBy('name')
                ->get(),
            'roles' => auth()->user()->isPlatformAdmin()
                ? User::ROLES
                : ['manager', 'staff', 'waiter', 'captain', 'chef', 'kitchen_manager'],
        ];
    }

    private function validatedData(Request $request, ?User $staff = null): array
    {
        $passwordRules = $staff
            ? ['nullable', 'string', 'min:12', 'confirmed']
            : ['required', 'string', 'min:12', 'confirmed'];

        $isPlatformAdmin = $request->user()->isPlatformAdmin();
        $allowedRoles = $isPlatformAdmin
            ? User::ROLES
            : ($staff?->is($request->user())
                ? User::ROLES
                : ['manager', 'staff', 'waiter', 'captain', 'chef', 'kitchen_manager']);
        $requiresBranch = \in_array($request->input('role'), ['staff', 'waiter', 'captain', 'chef', 'kitchen_manager'], true);
        $requiresOrganization = $request->input('role') === 'manager' || $requiresBranch;
        $organizationRules = $isPlatformAdmin
            ? [
                'nullable',
                Rule::requiredIf($requiresOrganization),
                'integer',
                'exists:organizations,id',
            ]
            : ['prohibited'];
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staff),
            ],
            'role' => ['required', Rule::in($allowedRoles)],
            'organization_id' => $organizationRules,
            'branch_id' => [
                'nullable',
                Rule::requiredIf($requiresBranch),
                'integer',
                Rule::exists('branches', 'id')
                    ->where('organization_id', $isPlatformAdmin
                        ? $request->input('organization_id')
                        : $request->user()->organization_id),
            ],
            'password' => $passwordRules,
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if (! $isPlatformAdmin) {
            $data['organization_id'] = $request->user()->organization_id;
        }

        return $data;
    }

    private function staffQuery()
    {
        return User::query()->when(
            ! auth()->user()->isPlatformAdmin(),
            fn ($query) => $query->where('organization_id', auth()->user()->organization_id ?? 0)
        );
    }

    private function organizationQuery()
    {
        return Organization::query()->when(
            ! auth()->user()->isPlatformAdmin(),
            fn ($query) => $query->whereKey(auth()->user()->organization_id ?? 0)
        );
    }

    private function ensureStaffAccess(User $staff): void
    {
        abort_unless(
            auth()->user()->isPlatformAdmin()
                || $staff->organization_id === auth()->user()->organization_id,
            404
        );
    }
}
