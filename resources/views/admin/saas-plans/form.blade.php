@extends('layouts.admin')

@section('title', $plan->exists ? 'Edit Subscription Plan' : 'Create Subscription Plan')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-1">{{ $plan->exists ? 'Edit plan' : 'Create plan' }}</h1>
    <p class="text-muted mb-0">Plans remain unpublished until you explicitly publish them and configure a price and Razorpay plan ID.</p>
</div>

<form method="POST" action="{{ $plan->exists ? route('admin.saas-plans.update', $plan) : route('admin.saas-plans.store') }}"
      class="admin-card">
    @csrf
    @if($plan->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="name">Plan name</label>
            <input id="name" name="name" required maxlength="120" value="{{ old('name', $plan->name) }}" class="form-control @error('name') is-invalid @enderror">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="slug">URL slug</label>
            <input id="slug" name="slug" maxlength="140" value="{{ old('slug', $plan->slug) }}" class="form-control @error('slug') is-invalid @enderror">
            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description" rows="2" class="form-control">{{ old('description', $plan->description) }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="price">Price</label>
            <input id="price" type="number" step="0.01" min="0" name="price" value="{{ old('price', $plan->price) }}" class="form-control @error('price') is-invalid @enderror">
            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="currency">Currency</label>
            <input id="currency" name="currency" maxlength="3" required value="{{ old('currency', $plan->currency ?: config('saas.currency')) }}" class="form-control @error('currency') is-invalid @enderror">
            @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="billing_interval">Billing interval</label>
            <select id="billing_interval" name="billing_interval" class="form-select">
                <option value="monthly" @selected(old('billing_interval', $plan->billing_interval) === 'monthly')>Monthly</option>
                <option value="yearly" @selected(old('billing_interval', $plan->billing_interval) === 'yearly')>Yearly</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="razorpay_plan_id">Razorpay plan ID</label>
            <input id="razorpay_plan_id" name="razorpay_plan_id" value="{{ old('razorpay_plan_id', $plan->razorpay_plan_id) }}" class="form-control @error('razorpay_plan_id') is-invalid @enderror">
            @error('razorpay_plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Create the matching recurring plan in your Razorpay account, then enter its ID here.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="features_text">Included features (one per line)</label>
            <textarea id="features_text" name="features_text" rows="4" class="form-control">{{ old('features_text', implode("\n", $plan->features ?? [])) }}</textarea>
        </div>
        @foreach(['branches' => 'max_branches', 'staff' => 'max_staff', 'products' => 'max_products', 'tables' => 'max_tables'] as $label => $field)
            <div class="col-md-3">
                <label class="form-label" for="{{ $field }}">Maximum {{ $label }}</label>
                <input id="{{ $field }}" type="number" min="1" name="{{ $field }}" value="{{ old($field, $plan->{$field}) }}" class="form-control @error($field) is-invalid @enderror">
                @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Leave blank for no limit.</div>
            </div>
        @endforeach
        <div class="col-md-4">
            <label class="form-label" for="sort_order">Display order</label>
            <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" class="form-control">
        </div>
        <div class="col-md-8 d-flex align-items-center">
            <div class="form-check">
                <input id="is_active" type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $plan->is_active))>
                <label class="form-check-label" for="is_active">Publish this plan on pricing and allow checkout</label>
            </div>
        </div>
    </div>
    @error('features')<div class="text-danger mt-2">{{ $message }}</div>@enderror
    <div class="d-flex gap-2 mt-4">
        <button class="btn btn-orange">Save plan</button>
        <a href="{{ route('admin.saas-plans.index') }}" class="btn btn-light border">Cancel</a>
    </div>
</form>
@endsection
