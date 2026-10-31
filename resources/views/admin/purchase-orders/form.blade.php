@extends('layouts.admin')
@section('title', 'New Purchase Order')
@section('content')
<div class="mb-4"><h3 class="fw-bold">New purchase order</h3><p class="text-muted mb-0">Amounts are calculated on the server. Receiving a purchase order adds its quantities to stock.</p></div>
<div class="admin-card"><form method="POST" action="{{ route('admin.purchase-orders.store') }}" data-ajax-form>@csrf
    <div class="row g-3 mb-4">
        <div class="col-md-4"><label class="form-label">Branch</label><select class="form-select" name="branch_id" data-po-branch required>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label">Supplier</label><select class="form-select" name="supplier_id" required>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach</select></div>
        <div class="col-md-4"><label class="form-label">Expected date</label><input class="form-control" type="date" name="expected_on"></div>
        <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
    </div>
    <h5 class="fw-bold">Items</h5><div data-po-items><div class="row g-2 align-items-end mb-2" data-po-row>
        <div class="col-md-6"><label class="form-label">Inventory item</label><select class="form-select" name="items[0][inventory_item_id]" data-po-item required>@foreach($items as $item)<option value="{{ $item->id }}" data-branch="{{ $item->branch_id }}">{{ $item->branch->name }} — {{ $item->name }} ({{ $item->unit }})</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">Quantity</label><input class="form-control" name="items[0][quantity]" type="number" min="0.001" step="0.001" required></div>
        <div class="col-md-2"><label class="form-label">Unit cost</label><input class="form-control" name="items[0][unit_cost]" type="number" min="0" step="0.01" required></div>
        <div class="col-md-2"><button class="btn btn-outline-danger w-100" type="button" data-remove-po-row disabled>Remove</button></div>
    </div></div>
    <template data-po-template><div class="row g-2 align-items-end mb-2" data-po-row>
        <div class="col-md-6"><label class="form-label">Inventory item</label><select class="form-select" name="items[__index__][inventory_item_id]" data-po-item required>@foreach($items as $item)<option value="{{ $item->id }}" data-branch="{{ $item->branch_id }}">{{ $item->branch->name }} — {{ $item->name }} ({{ $item->unit }})</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">Quantity</label><input class="form-control" name="items[__index__][quantity]" type="number" min="0.001" step="0.001" required></div>
        <div class="col-md-2"><label class="form-label">Unit cost</label><input class="form-control" name="items[__index__][unit_cost]" type="number" min="0" step="0.01" required></div>
        <div class="col-md-2"><button class="btn btn-outline-danger w-100" type="button" data-remove-po-row>Remove</button></div>
    </div></template>
    <button class="btn btn-sm btn-outline-secondary mt-2" type="button" data-add-po-row><i class="bi bi-plus-lg me-1"></i>Add item</button>
    <div class="mt-4"><button class="btn btn-orange">Create draft purchase order</button> <a class="btn btn-outline-secondary" href="{{ route('admin.purchase-orders.index') }}">Cancel</a></div>
</form></div>
@endsection
