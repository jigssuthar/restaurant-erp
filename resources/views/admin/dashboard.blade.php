@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1">Dashboard Overview</h3>
        <p class="text-muted mb-0">DineCentral management overview.</p>
    </div>
    <div class="text-muted small">{{ now()->format('d M Y') }}</div>
</div>

<div class="admin-card">
    <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.dashboard') }}" data-ajax-filter>
        <div class="col-md-4"><label class="form-label">From date</label><input class="form-control" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></div>
        <div class="col-md-4"><label class="form-label">To date</label><input class="form-control" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></div>
        <div class="col-md-4 d-flex gap-2"><button class="btn btn-orange">Update dashboard</button><a href="{{ route('admin.dashboard') }}" class="btn btn-light border">Reset</a></div>
    </form>
</div>

<div class="row g-4 mb-4">
    @foreach($stats as [$label, $value, $icon, $color, $route])
        <div class="col-xl-4 col-md-6">
            @if($route)
                <a href="{{ route($route) }}" class="text-decoration-none text-reset">
            @endif
            <div class="admin-card h-100 mb-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-2">{{ $label }}</p>
                        <h3 class="fw-bold mb-0">{{ $value }}</h3>
                    </div>
                    <div class="stat-icon {{ $color }}"><i class="bi {{ $icon }}"></i></div>
                </div>
            </div>
            @if($route)
                </a>
            @endif
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="admin-card h-100">
            <h5 class="fw-bold mb-4">Order summary</h5>
            @foreach(['pending', 'held', 'preparing', 'ready', 'completed', 'cancelled'] as $status)
                <div class="d-flex justify-content-between mb-3">
                    <span>{{ ucfirst($status) }}</span>
                    <span class="badge bg-light text-dark">{{ $orderStatuses->get($status, 0) }}</span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="col-lg-8">
        <div class="admin-table-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Recent orders</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-warning">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>ORDER</th><th>BRANCH</th><th>TOTAL</th><th>STATUS</th></tr></thead>
                    <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td>{{ $order->branch->name }}</td>
                            <td>{{ $order->currency_code }} {{ number_format((float) $order->total, 2) }}</td>
                            <td>{{ ucfirst($order->status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No orders yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
