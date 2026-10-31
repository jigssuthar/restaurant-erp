@extends('layouts.admin')
@section('title', 'Purchase Orders')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4"><div><h3 class="fw-bold mb-1">Suppliers & Purchase Orders</h3><p class="text-muted mb-0">Create purchase orders and receive stock directly into inventory.</p></div><a class="btn btn-orange" href="{{ route('admin.purchase-orders.create') }}"><i class="bi bi-plus-lg me-2"></i>New purchase order</a></div>
@include('admin.partials.feedback')
<div class="admin-table-card"><form class="row g-2 mb-4" method="GET" action="{{ route('admin.purchase-orders.index') }}" data-ajax-filter><div class="col-md-8"><select class="form-select" name="status"><option value="">All statuses</option>@foreach(\App\Models\PurchaseOrder::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div><div class="col-md-4"><button class="btn btn-outline-secondary w-100">Filter</button></div></form>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>PURCHASE ORDER</th><th>BRANCH / SUPPLIER</th><th>EXPECTED</th><th>LINES</th><th>TOTAL</th><th>STATUS / ACTION</th></tr></thead><tbody>
    @forelse($orders as $order)<tr><td class="fw-semibold"><a href="{{ route('admin.purchase-orders.show', $order) }}">{{ $order->purchase_order_number }}</a><br><small class="text-muted">{{ $order->created_at->format('d M Y') }}</small></td><td>{{ $order->branch->name }}<br><small>{{ $order->supplier->name }}</small></td><td>{{ $order->expected_on?->format('d M Y') ?: '—' }}</td><td>{{ $order->items_count }}</td><td>{{ $order->currency_code }} {{ number_format((float) $order->subtotal, 2) }}</td><td><span class="badge bg-light text-dark">{{ ucfirst($order->status) }}</span>
        @if(in_array($order->status, ['draft','ordered']))<form class="d-flex gap-1 mt-2" method="POST" action="{{ route('admin.purchase-orders.status', $order) }}" data-ajax-form>@csrf @method('PATCH')<select class="form-select form-select-sm" name="status">@if($order->status === 'draft')<option value="ordered">Mark ordered</option>@endif @if($order->status === 'ordered')<option value="received">Receive stock</option>@endif<option value="cancelled">Cancel</option></select><button class="btn btn-sm btn-outline-primary">Apply</button></form>@endif
    </td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-5">No purchase orders found.</td></tr>@endforelse
    </tbody></table></div>{{ $orders->links() }}
</div>
@endsection
