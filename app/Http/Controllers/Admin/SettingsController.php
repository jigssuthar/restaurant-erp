<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $organizations = Organization::query()
            ->when(
                ! $user->isPlatformAdmin(),
                fn ($query) => $query->whereKey($user->organization_id ?? 0)
            )
            ->orderBy('name')
            ->get(['id', 'name']);

        if (! $user->isPlatformAdmin() && ! $user->organization_id) {
            abort(403);
        }

        $organization = $user->isPlatformAdmin()
            ? $organizations->firstWhere('id', $request->integer('organization_id')) ?? $organizations->first()
            : $organizations->first();

        $settings = $organization
            ? OrganizationSetting::firstOrNew(
                ['organization_id' => $organization->id],
                [
                    'currency_code' => 'INR',
                    'tax_rate' => 0,
                    'service_charge_rate' => 0,
                    'delivery_fee' => 0,
                ]
            )
            : null;

        return view('admin.settings.index', compact('organizations', 'organization', 'settings'));
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $request->merge([
            'currency_code' => strtoupper(trim((string) $request->input('currency_code'))),
        ]);

        $organizationRule = $user->isPlatformAdmin()
            ? Rule::exists('organizations', 'id')
            : Rule::in([$user->organization_id]);

        $data = $request->validate([
            'organization_id' => ['required', 'integer', $organizationRule],
            'currency_code' => ['required', 'string', 'regex:/^[A-Z]{3}$/'],
            'tax_label' => ['nullable', 'string', 'max:60'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'service_charge_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'delivery_fee' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'receipt_header' => ['nullable', 'string', 'max:255'],
            'receipt_footer' => ['nullable', 'string', 'max:2000'],
        ]);

        OrganizationSetting::updateOrCreate(
            ['organization_id' => $data['organization_id']],
            collect($data)->except('organization_id')->all()
        );

        return redirect()
            ->route('admin.settings.index', ['organization_id' => $data['organization_id']])
            ->with('success', 'Organization settings saved successfully.');
    }
}
