@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">Restaurant Settings</h3><p class="text-muted mb-0">Organization-wide pricing, currency, and receipt defaults.</p></div>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

@if($organizations->isEmpty())
    <div class="alert alert-warning">Create an organization before configuring settings.
        <a href="{{ route('admin.organizations.create') }}" class="alert-link">Add organization</a>
    </div>
@elseif($organization && $settings)
    @if(auth()->user()->isPlatformAdmin())
        <form method="GET" class="admin-card mb-4">
            <label for="organization_id" class="form-label">Organization</label>
            <div class="d-flex gap-2">
                <select id="organization_id" name="organization_id" class="form-select">
                    @foreach($organizations as $item)
                        <option value="{{ $item->id }}" @selected((string) request('organization_id', $organization->id) === (string) $item->id)>{{ $item->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-orange">Select</button>
            </div>
        </form>
    @endif
    <div class="admin-card">
        <h5 class="fw-bold mb-1">{{ $organization->name }}</h5>
        <p class="text-muted mb-4">These settings apply across all branches in this organization.</p>
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="organization_id" value="{{ $organization->id }}">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="currency_code" class="form-label">Currency code <span class="text-danger">*</span></label>
                    <input id="currency_code" name="currency_code" value="{{ old('currency_code', $settings->currency_code ?? 'INR') }}"
                           class="form-control @error('currency_code') is-invalid @enderror" maxlength="3" pattern="[A-Za-z]{3}" required>
                    <div class="form-text">Use a three-letter ISO code such as INR or USD.</div>
                    @error('currency_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="tax_label" class="form-label">Tax label</label>
                    <input id="tax_label" name="tax_label" value="{{ old('tax_label', $settings->tax_label ?? '') }}" maxlength="60"
                           class="form-control @error('tax_label') is-invalid @enderror" placeholder="GST">
                    @error('tax_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="tax_rate" class="form-label">Tax rate (%) <span class="text-danger">*</span></label>
                    <input id="tax_rate" type="number" name="tax_rate" value="{{ old('tax_rate', $settings->tax_rate ?? 0) }}"
                           min="0" max="100" step="0.01" required class="form-control @error('tax_rate') is-invalid @enderror">
                    @error('tax_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="service_charge_rate" class="form-label">Service charge (%) <span class="text-danger">*</span></label>
                    <input id="service_charge_rate" type="number" name="service_charge_rate"
                           value="{{ old('service_charge_rate', $settings->service_charge_rate ?? 0) }}"
                           min="0" max="100" step="0.01" required class="form-control @error('service_charge_rate') is-invalid @enderror">
                    @error('service_charge_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="delivery_fee" class="form-label">Delivery fee (<span id="currencyPreview">{{ old('currency_code', $settings->currency_code ?? 'INR') }}</span>)</label>
                    <input id="delivery_fee" type="number" name="delivery_fee" value="{{ old('delivery_fee', $settings->delivery_fee ?? 0) }}"
                           min="0" step="0.01" required class="form-control @error('delivery_fee') is-invalid @enderror">
                    @error('delivery_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="receipt_header" class="form-label">Receipt header</label>
                    <input id="receipt_header" name="receipt_header" value="{{ old('receipt_header', $settings->receipt_header ?? '') }}"
                           maxlength="255" class="form-control @error('receipt_header') is-invalid @enderror">
                    @error('receipt_header')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="receipt_footer" class="form-label">Receipt footer</label>
                    <textarea id="receipt_footer" name="receipt_footer" rows="3" maxlength="2000"
                              class="form-control @error('receipt_footer') is-invalid @enderror">{{ old('receipt_footer', $settings->receipt_footer ?? '') }}</textarea>
                    @error('receipt_footer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="alert alert-light border mt-4 mb-0">
                Order calculations apply tax to the item subtotal, calculate service charge on the item subtotal, and add delivery fee only to delivery orders. No compounding tax or discounts are applied.
            </div>
            <div class="mt-4"><button type="submit" class="btn btn-orange">Save Settings</button></div>
        </form>
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const currency = document.getElementById('currency_code');
    const preview = document.getElementById('currencyPreview');
    currency?.addEventListener('input', () => {
        preview.textContent = currency.value.toUpperCase() || 'INR';
    });
});
</script>
@endpush
