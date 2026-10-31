@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">Orders</h3><p class="text-muted mb-0">Track dine-in, takeaway, and delivery orders.</p></div>
    <a href="{{ route('admin.orders.create') }}" class="btn btn-orange"><i class="bi bi-plus-lg me-2"></i>New Order</a>
</div>
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
<div class="admin-table-card">
    <form method="GET" class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6"><input type="search" name="search" class="form-control" placeholder="Search order or customer" value="{{ request('search') }}"></div>
        <div class="col-lg-3 col-md-6"><select name="branch_id" class="form-select">
            <option value="">All branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select></div>
        <div class="col-lg-2 col-md-6"><select name="status" class="form-select">
            <option value="">All statuses</option>
            @foreach(\App\Models\Order::STATUSES as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select></div>
        <div class="col-lg-2 col-md-6"><select name="order_type" class="form-select">
            <option value="">All types</option>
            @foreach(\App\Models\Order::TYPES as $type)
                <option value="{{ $type }}" @selected(request('order_type') === $type)>{{ str_replace('_', ' ', ucfirst($type)) }}</option>
            @endforeach
        </select></div>
        <div class="col-lg-1 col-md-6"><button type="submit" class="btn btn-orange w-100">Go</button></div>
    </form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>ORDER</th><th>BRANCH / TABLE</th><th>CUSTOMER</th><th>TYPE</th><th>TOTAL</th><th>STATUS</th><th>PAYMENT</th></tr></thead>
        <tbody>
        @forelse($orders as $order)
            <tr>
                <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-decoration-none">{{ $order->order_number }}</a>
                    <br><small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small></td>
                <td>{{ $order->branch->name }}<br><small class="text-muted">{{ $order->diningTable?->table_number ? 'Table '.$order->diningTable->table_number : '—' }}</small></td>
                <td>{{ $order->customer_name ?: 'Walk-in' }}<br><small class="text-muted">{{ $order->customer_phone ?: '' }}</small></td>
                <td>{{ str_replace('_', ' ', ucfirst($order->order_type)) }}</td>
                <td>{{ $order->currency_code }} {{ number_format((float) $order->total, 2) }}</td>
                <td><span class="badge {{ $order->status === 'completed' ? 'bg-success' : ($order->status === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') }}">
                    {{ ucfirst($order->status) }}</span></td>
                <td><span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($order->payment_status) }}</span></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-5">No orders found.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $orders->links() }}
</div>
@endsection
