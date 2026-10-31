<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $organizations = Organization::with(['currentSubscription.plan'])->withCount('branches')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%")
                      ->orWhere('phone', 'like', "%$search%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.organizations.index', compact('organizations'));
    }

    public function create()
    {
        return view('admin.organizations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'gst_number' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        $data['slug'] = Str::slug($data['name'])
            . '-' . Str::lower(Str::random(6));

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')
                ->store('organizations', 'public');
        }

        Organization::create($data);

        return redirect()
            ->route('admin.organizations.index')
            ->with('success', 'Organization created successfully.');
    }

    public function show(Organization $organization)
    {
        $organization->loadCount(['branches', 'users']);

        return view('admin.organizations.show', compact('organization'));
    }

    public function edit(Organization $organization)
    {
        return view('admin.organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'gst_number' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('logo')) {

            if ($organization->logo) {
                Storage::disk('public')->delete($organization->logo);
            }

            $data['logo'] = $request->file('logo')
                ->store('organizations', 'public');
        }

        $organization->update($data);

        return redirect()
            ->route('admin.organizations.index')
            ->with('success', 'Organization updated successfully.');
    }

    public function destroy(Organization $organization)
    {
        if ($organization->branches()->exists() ||
            $organization->users()->exists()) {

            return back()->with(
                'error',
                'Organization cannot be deleted because branches or users exist.'
            );
        }

        if ($organization->logo) {
            Storage::disk('public')->delete($organization->logo);
        }

        $organization->delete();

        return redirect()
            ->route('admin.organizations.index')
            ->with('success', 'Organization deleted successfully.');
    }
}
