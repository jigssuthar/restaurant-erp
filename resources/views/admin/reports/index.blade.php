@extends('layouts.admin')
@section('title', 'Advanced Reports')
@section('content')
<div class="mb-4"><h3 class="fw-bold mb-1">Advanced Reports</h3><p class="text-muted mb-0">Review sales, order trends, popular items, and stock risk by date and branch.</p></div>
<div class="admin-card">
    <form class="row g-3" method="GET" action="{{ route('admin.reports.index') }}" data-ajax-filter>
        <div class="col-md-3"><label class="form-label">From</label><input class="form-control" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></div>
        <div class="col-md-3"><label class="form-label">To</label><input class="form-control" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></div>
        <div class="col-md-4"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? '') == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
        <div class="col-md-2 d-flex align-items-end"><button class="btn btn-orange w-100">Run report</button></div>
    </form>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="admin-card mb-0"><div class="text-muted">Orders</div><h3 class="fw-bold mb-0">{{ number_format($orderCount) }}</h3></div></div>
    <div class="col-md-4"><div class="admin-card mb-0"><div class="text-muted">Net payments by currency</div>@forelse($sales as $sale)<div class="fw-bold">{{ $sale->currency_code }} {{ number_format((float) $sale->total, 2) }}</div>@empty<div class="fw-bold">No payments received</div>@endforelse</div></div>
    <div class="col-md-4"><div class="admin-card mb-0"><div class="text-muted">Cancelled orders</div><h3 class="fw-bold mb-0">{{ number_format($cancelledCount) }}</h3></div></div>
</div>
<div class="row g-4">
    <div class="col-lg-6"><div class="admin-table-card"><h5 class="fw-bold mb-3">Order status breakdown</h5><table class="table"><tbody>@foreach(\App\Models\Order::STATUSES as $status)<tr><td>{{ ucfirst($status) }}</td><td class="text-end fw-semibold">{{ number_format($statusCounts->get($status, 0)) }}</td></tr>@endforeach</tbody></table></div></div>
    <div class="col-lg-6"><div class="admin-table-card"><h5 class="fw-bold mb-3">Top-selling items</h5><div class="table-responsive"><table class="table"><thead><tr><th>ITEM</th><th>QTY SOLD</th></tr></thead><tbody>@forelse($topItems as $item)<tr><td>{{ $item->product_name }}</td><td>{{ number_format((float) $item->quantity_sold, 0) }}</td></tr>@empty<tr><td colspan="2" class="text-center text-muted">No order items for this period.</td></tr>@endforelse</tbody></table></div></div></div>
    <div class="col-12"><div class="admin-table-card"><h5 class="fw-bold mb-3">Low-stock watchlist</h5><div class="table-responsive"><table class="table"><thead><tr><th>ITEM</th><th>BRANCH</th><th>ON HAND</th><th>REORDER AT</th></tr></thead><tbody>@forelse($lowStock as $item)<tr><td>{{ $item->name }}</td><td>{{ $item->branch->name }}</td><td>{{ number_format((float) $item->quantity_on_hand, 3) }} {{ $item->unit }}</td><td>{{ number_format((float) $item->reorder_level, 3) }} {{ $item->unit }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">No low-stock items.</td></tr>@endforelse</tbody></table></div></div></div>
</div>
@endsection
