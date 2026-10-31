<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaasPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaasPlanController extends Controller
{
    public function index()
    {
        $plans = SaasPlan::withCount('subscriptions')->orderBy('sort_order')->orderBy('name')->paginate(15);

        return view('admin.saas-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.saas-plans.form', ['plan' => new SaasPlan()]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        SaasPlan::create($data);

        return redirect()->route('admin.saas-plans.index')->with('success', 'Plan saved.');
    }

    public function edit(SaasPlan $saasPlan)
    {
        return view('admin.saas-plans.form', ['plan' => $saasPlan]);
    }

    public function update(Request $request, SaasPlan $saasPlan)
    {
        $data = $this->validatedData($request, $saasPlan);
        if ($saasPlan->subscriptions()->exists()
            && (
                (float) ($data['price'] ?? null) !== (float) $saasPlan->price
                || $data['currency'] !== $saasPlan->currency
                || $data['billing_interval'] !== $saasPlan->billing_interval
                || ($data['razorpay_plan_id'] ?? null) !== $saasPlan->razorpay_plan_id
            )) {
            return back()->withInput()->withErrors([
                'price' => 'Billing terms cannot be changed after subscriptions exist. Create a new plan for new pricing.',
            ]);
        }

        $saasPlan->update($data);

        return redirect()->route('admin.saas-plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(SaasPlan $saasPlan)
    {
        if ($saasPlan->subscriptions()->exists()) {
            return back()->with('error', 'A plan attached to subscriptions cannot be deleted. Deactivate it instead.');
        }

        $saasPlan->delete();

        return redirect()->route('admin.saas-plans.index')->with('success', 'Plan deleted.');
    }

    private function validatedData(Request $request, ?SaasPlan $plan = null): array
    {
        $request->merge([
            'currency' => strtoupper(trim((string) $request->input('currency', config('saas.currency', 'INR')))),
            'slug' => Str::slug(trim((string) $request->input('slug', '')) ?: (string) $request->input('name', '')),
            'features' => array_values(array_filter(array_map(
                'trim',
                preg_split('/\r\n|\r|\n/', (string) $request->input('features_text', '')) ?: []
            ))),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('saas_plans', 'slug')->ignore($plan)],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', Rule::requiredIf($request->boolean('is_active'))],
            'currency' => [
                'required',
                'string',
                'regex:/^[A-Z]{3}$/',
                Rule::in([strtoupper((string) config('saas.currency', 'INR'))]),
            ],
            'billing_interval' => ['required', Rule::in(['monthly', 'yearly'])],
            'razorpay_plan_id' => ['nullable', 'string', 'max:255', Rule::requiredIf($request->boolean('is_active'))],
            'max_branches' => ['nullable', 'integer', 'min:1'],
            'max_staff' => ['nullable', 'integer', 'min:1'],
            'max_products' => ['nullable', 'integer', 'min:1'],
            'max_tables' => ['nullable', 'integer', 'min:1'],
            'features' => ['array'],
            'features.*' => ['string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:99999'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
