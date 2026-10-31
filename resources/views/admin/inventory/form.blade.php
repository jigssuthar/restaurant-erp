@extends('layouts.admin')
@section('title', $item->exists ? 'Edit Inventory Item' : 'Add Inventory Item')
@section('content')
<div class="mb-4"><h3 class="fw-bold">{{ $item->exists ? 'Edit inventory item' : 'Add inventory item' }}</h3><p class="text-muted mb-0">Use stock adjustment to change on-hand quantity after setup.</p></div>
<div class="admin-card"><form method="POST" action="{{ $item->exists ? route('admin.inventory.update', $item) : route('admin.inventory.store') }}" data-ajax-form>@csrf @if($item->exists) @method('PUT') @endif
    <div class="row g-3">
        @if(!$item->exists)<div class="col-md-6"><label class="form-label">Branch</label><select class="form-select" name="branch_id" required>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>@endif
        <div class="col-md-6"><label class="form-label">Item name</label><input class="form-control" name="name" value="{{ old('name', $item->name) }}" required></div>
        <div class="col-md-3"><label class="form-label">SKU</label><input class="form-control" name="sku" value="{{ old('sku', $item->sku) }}"></div>
        <div class="col-md-3"><label class="form-label">Unit</label><input class="form-control" name="unit" value="{{ old('unit', $item->unit ?: 'piece') }}" required placeholder="kg, litre, piece"></div>
        @if(!$item->exists)<div class="col-md-3"><label class="form-label">Opening quantity</label><input class="form-control" type="number" min="0" step="0.001" name="quantity_on_hand" value="{{ old('quantity_on_hand', 0) }}" required></div>@endif
        <div class="col-md-3"><label class="form-label">Low-stock threshold</label><input class="form-control" type="number" min="0" step="0.001" name="reorder_level" value="{{ old('reorder_level', $item->reorder_level ?? 0) }}" required></div>
        <div class="col-md-3"><label class="form-label">Unit cost</label><input class="form-control" type="number" min="0" step="0.01" name="unit_cost" value="{{ old('unit_cost', $item->unit_cost ?? 0) }}" required></div>
        <div class="col-md-3"><label class="form-label">Active</label><select class="form-select" name="is_active"><option value="1" @selected(old('is_active', $item->is_active ?? true))>Yes</option><option value="0" @selected(!old('is_active', $item->is_active ?? true))>No</option></select></div>
    </div><div class="mt-4"><button class="btn btn-orange">Save item</button> <a class="btn btn-outline-secondary" href="{{ route('admin.inventory.index') }}">Cancel</a></div>
</form></div>
@endsection
