@extends('layouts.admin')
@section('title', $supplier->exists ? 'Edit Supplier' : 'Add Supplier')
@section('content')
<div class="mb-4"><h3 class="fw-bold">{{ $supplier->exists ? 'Edit supplier' : 'Add supplier' }}</h3><p class="text-muted mb-0">Keep supplier details up to date for purchase orders.</p></div>
<div class="admin-card"><form method="POST" action="{{ $supplier->exists ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}" data-ajax-form>@csrf @if($supplier->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Supplier name</label><input class="form-control" name="name" value="{{ old('name', $supplier->name) }}" required></div>
        <div class="col-md-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $supplier->email) }}"></div>
        <div class="col-md-3"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone', $supplier->phone) }}"></div>
        <div class="col-md-4"><label class="form-label">Tax reference</label><input class="form-control" name="tax_reference" value="{{ old('tax_reference', $supplier->tax_reference) }}"></div>
        <div class="col-md-8"><label class="form-label">Address</label><input class="form-control" name="address" value="{{ old('address', $supplier->address) }}"></div>
        <div class="col-md-8"><label class="form-label">Notes</label><textarea class="form-control" rows="2" name="notes">{{ old('notes', $supplier->notes) }}</textarea></div>
        <div class="col-md-4"><label class="form-label">Active</label><select class="form-select" name="is_active"><option value="1" @selected(old('is_active', $supplier->is_active ?? true))>Yes</option><option value="0" @selected(!old('is_active', $supplier->is_active ?? true))>No</option></select></div>
    </div><div class="mt-4"><button class="btn btn-orange">Save supplier</button> <a class="btn btn-outline-secondary" href="{{ route('admin.suppliers.index') }}">Cancel</a></div>
</form></div>
@endsection
