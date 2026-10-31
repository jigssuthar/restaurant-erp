@extends('layouts.admin')
@section('title', 'Inventory')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4"><div><h3 class="fw-bold mb-1">Inventory & Stock</h3><p class="text-muted mb-0">Track on-hand quantities, low-stock thresholds, and stock movement history.</p></div><a class="btn btn-orange" href="{{ route('admin.inventory.create') }}"><i class="bi bi-plus-lg me-2"></i>Add item</a></div>
@include('admin.partials.feedback')
<div class="admin-table-card">
    <form class="row g-2 mb-4" method="GET" action="{{ route('admin.inventory.index') }}" data-ajax-filter>
        <div class="col-md-4"><input class="form-control" type="search" name="search" placeholder="Search name or SKU" value="{{ request('search') }}"></div><div class="col-md-3"><select class="form-select" name="branch_id"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div><div class="col-md-3"><label class="form-check mt-2"><input class="form-check-input" type="checkbox" name="low_stock" value="1" @checked(request()->boolean('low_stock'))><span class="form-check-label">Low stock only</span></label></div><div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div>
    </form>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>ITEM</th><th>BRANCH</th><th>ON HAND</th><th>REORDER AT</th><th>UNIT COST</th><th>STOCK ADJUSTMENT</th><th></th></tr></thead><tbody>
    @forelse($items as $item)
        <tr><td class="fw-semibold">{{ $item->name }}<br><small class="text-muted">{{ $item->sku ?: 'No SKU' }} · {{ $item->unit }}</small></td><td>{{ $item->branch->name }}</td><td><span class="badge {{ $item->isLowStock() ? 'bg-danger' : 'bg-success' }}">{{ number_format((float) $item->quantity_on_hand, 3) }} {{ $item->unit }}</span></td><td>{{ number_format((float) $item->reorder_level, 3) }}</td><td>{{ number_format((float) $item->unit_cost, 2) }}</td><td><form method="POST" action="{{ route('admin.inventory.adjust', $item) }}" data-ajax-form class="d-flex flex-wrap gap-1">@csrf @method('PATCH')<select class="form-select form-select-sm" name="movement_type"><option value="in">Add</option><option value="out">Remove</option></select><input class="form-control form-control-sm" style="max-width:90px" type="number" name="quantity" min="0.001" step="0.001" placeholder="Qty" required><input class="form-control form-control-sm" style="max-width:135px" name="reason" placeholder="Reason" required><button class="btn btn-sm btn-outline-primary">Update</button></form><a class="small d-inline-block mt-1" href="{{ route('admin.inventory.movements', $item) }}">Stock history</a></td><td><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.inventory.edit', $item) }}">Edit</a></td></tr>
    @empty<tr><td colspan="7" class="text-center text-muted py-5">No inventory items found.</td></tr>@endforelse
    </tbody></table></div>{{ $items->links() }}
</div>
@endsection
