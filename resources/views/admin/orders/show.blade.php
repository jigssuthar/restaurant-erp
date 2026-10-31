@extends('layouts.admin')
@section('title', $order->order_number)
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 no-print">
    <div><h3 class="fw-bold mb-1">{{ $order->order_number }}</h3>
        <p class="text-muted mb-0">{{ $order->created_at->format('d M Y, H:i') }} · {{ $order->branch->organization->name }} — {{ $order->branch->name }}</p></div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-orange" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print receipt</button>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-light border">Back to Orders</a>
    </div>
</div>
@if(session('success'))
    <div class="alert alert-success no-print">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger no-print"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="row g-4">
    <div class="col-lg-8 receipt-content">
        <div class="admin-card">
            <div class="text-center mb-4">
                <h4 class="fw-bold mb-1">{{ $order->branch->organization->settings?->receipt_header ?: $order->branch->organization->name }}</h4>
                <div>{{ $order->branch->organization->address }}</div>
                <div>{{ $order->branch->organization->phone }}{{ $order->branch->organization->email ? ' · '.$order->branch->organization->email : '' }}</div>
                @if($order->branch->organization->settings?->receipt_header)
                    <div>{{ $order->branch->organization->name }}</div>
                @endif
                <hr>
                <div>{{ $order->order_number }} · {{ $order->created_at->format('d M Y, H:i') }}</div>
                <div>{{ ucfirst($order->payment_status) }}{{ $order->payment_method ? ' · '.strtoupper($order->payment_method) : '' }}</div>
            </div>
            <h5 class="fw-bold mb-3">Order items</h5>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>PRODUCT</th><th>QTY</th><th>UNIT PRICE</th><th class="text-end">TOTAL</th></tr></thead>
                <tbody>
                @foreach($order->items as $item)
                    <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td>
                        <td>{{ $order->currency_code }} {{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="text-end">{{ $order->currency_code }} {{ number_format((float) $item->line_total, 2) }}</td></tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr><th colspan="3" class="text-end">Subtotal</th><th class="text-end">{{ $order->currency_code }} {{ number_format((float) $order->subtotal, 2) }}</th></tr>
                    @if($order->tax_amount > 0)
                        <tr><td colspan="3" class="text-end">{{ $order->tax_label ?: 'Tax' }} ({{ number_format((float) $order->tax_rate, 2) }}%)</td>
                            <td class="text-end">{{ $order->currency_code }} {{ number_format((float) $order->tax_amount, 2) }}</td></tr>
                    @endif
                    @if($order->service_charge > 0)
                        <tr><td colspan="3" class="text-end">Service charge ({{ number_format((float) $order->service_charge_rate, 2) }}%)</td>
                            <td class="text-end">{{ $order->currency_code }} {{ number_format((float) $order->service_charge, 2) }}</td></tr>
                    @endif
                    @if($order->delivery_fee > 0)
                        <tr><td colspan="3" class="text-end">Delivery fee</td>
                            <td class="text-end">{{ $order->currency_code }} {{ number_format((float) $order->delivery_fee, 2) }}</td></tr>
                    @endif
                    <tr><th colspan="3" class="text-end">Total</th>
                        <th class="text-end">{{ $order->currency_code }} {{ number_format((float) $order->total, 2) }}</th></tr>
                </tfoot>
            </table></div>
            @if($order->notes)<p class="mb-0"><strong>Notes:</strong> {{ $order->notes }}</p>@endif
            @if($order->branch->organization->settings?->receipt_footer)
                <div class="text-center mt-4">{{ $order->branch->organization->settings->receipt_footer }}</div>
            @endif
        </div>
        <div class="admin-card">
            <h5 class="fw-bold mb-3">Customer and fulfillment</h5>
            <div class="row g-3">
                <div class="col-md-6"><strong>Type</strong><div>{{ str_replace('_', ' ', ucfirst($order->order_type)) }}</div></div>
                <div class="col-md-6"><strong>Table</strong><div>{{ $order->diningTable?->table_number ?? '—' }}</div></div>
                <div class="col-md-6"><strong>Customer</strong><div>{{ $order->customer_name ?: 'Walk-in' }}</div></div>
                <div class="col-md-6"><strong>Phone</strong><div>{{ $order->customer_phone ?: '—' }}</div></div>
                @if($order->order_type === 'delivery')
                    <div class="col-12"><strong>Delivery address</strong><div>{{ $order->delivery_address }}</div></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4 order-actions no-print">
        <div class="admin-card">
            <h5 class="fw-bold mb-3">Order status</h5>
            <p><span class="badge bg-warning text-dark">{{ ucfirst($order->status) }}</span></p>
            @php
                $nextStatuses = [
                    'pending' => ['preparing', 'cancelled'],
                    'preparing' => ['ready', 'cancelled'],
                    'ready' => ['completed', 'cancelled'],
                    'completed' => [],
                    'cancelled' => [],
                ][$order->status] ?? [];
            @endphp
            @if($nextStatuses)
                <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                    @csrf @method('PATCH')
                    <label for="status" class="form-label">Move to</label>
                    <select id="status" name="status" class="form-select mb-3" required>
                        @foreach($nextStatuses as $status)<option value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach
                    </select>
                    <button class="btn btn-orange w-100" type="submit">Update status</button>
                </form>
            @else
                <p class="text-muted small mb-0">This order is closed.</p>
            @endif
        </div>
        <div class="admin-card">
            <h5 class="fw-bold mb-3">Payment</h5>
            <p class="mb-1"><strong>{{ ucfirst($order->payment_status) }}</strong>
                @if($order->payment_method) · {{ strtoupper($order->payment_method) }} @endif</p>
            @if($order->paid_at)<small class="text-muted">Paid {{ $order->paid_at->format('d M Y, H:i') }}</small>@endif
            @if($order->payment_status === 'pending' && $order->status !== 'cancelled')
                <form action="{{ route('admin.orders.payment', $order) }}" method="POST" class="mt-3">
                    @csrf @method('PATCH')
                    <label for="payment_method" class="form-label">Record payment</label>
                    <select id="payment_method" name="payment_method" class="form-select mb-3" required>
                        @foreach(\App\Models\Order::PAYMENT_METHODS as $method)<option value="{{ $method }}">{{ strtoupper($method) }}</option>@endforeach
                    </select>
                    <button class="btn btn-outline-success w-100" type="submit">Mark as paid</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .admin-sidebar, .admin-header, .sidebar-overlay, .no-print { display: none !important; }
    .admin-main { margin: 0 !important; width: 100% !important; }
    .admin-content { padding: 0 !important; }
    .receipt-content { width: 100% !important; }
}
</style>
@endpush
