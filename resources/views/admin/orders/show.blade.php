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
<div class="admin-card no-print">
    <h5 class="fw-bold mb-3">Payment ledger</h5>
    <div class="d-flex justify-content-between"><span>Paid</span><strong>{{ $order->currency_code }} {{ number_format($paidAmount, 2) }}</strong></div>
    @if($refundedAmount > 0)
        <div class="d-flex justify-content-between"><span>Refunded</span><strong>{{ $order->currency_code }} {{ number_format($refundedAmount, 2) }}</strong></div>
    @endif
    <div class="d-flex justify-content-between mb-3"><span>Balance due</span><strong>{{ $order->currency_code }} {{ number_format($remainingBalance, 2) }}</strong></div>
    @if($order->payments->isEmpty())
        <p class="text-muted small">No payment transactions recorded yet.</p>
    @else
        <div class="table-responsive"><table class="table table-sm"><thead><tr><th>TIME</th><th>TYPE / METHOD</th><th>REFERENCE</th><th class="text-end">AMOUNT</th></tr></thead><tbody>
            @foreach($order->payments as $payment)
                <tr><td>{{ $payment->recorded_at->format('d M H:i') }}</td><td>{{ ucfirst($payment->transaction_type) }} · {{ strtoupper($payment->payment_method) }}</td><td>{{ $payment->reference ?: $payment->notes ?: '—' }}<br><small>{{ $payment->creator?->name ?: 'System' }}</small></td><td class="text-end">{{ $payment->transaction_type === 'refund' ? '-' : '' }}{{ $order->currency_code }} {{ number_format((float) $payment->amount, 2) }}</td></tr>
            @endforeach
        </tbody></table></div>
    @endif
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
                        @if($order->invoice_number)<div>Invoice {{ $order->invoice_number }}</div>@endif
                        <div>{{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}{{ $order->payment_method ? ' · '.strtoupper($order->payment_method) : '' }}</div>
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
                    @if($order->discount_amount > 0)
                        <tr><td colspan="3" class="text-end">Discount{{ $order->discount_type === 'percentage' ? ' ('.number_format((float) $order->discount_value, 2).'%)' : '' }}</td>
                            <td class="text-end">-{{ $order->currency_code }} {{ number_format((float) $order->discount_amount, 2) }}</td></tr>
                    @endif
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
            <p><span class="badge bg-warning text-dark">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span></p>
            @php
                $nextStatuses = [
                    'pending' => ['held', 'preparing', 'cancelled'],
                    'held' => ['pending', 'cancelled'],
                    'preparing' => ['ready', 'cancelled'],
                    'ready' => ['completed', 'cancelled'],
                    'completed' => [],
                    'cancelled' => [],
                ][$order->status] ?? [];
            @endphp
            @if($nextStatuses)
                <form action="{{ route('admin.orders.status', $order) }}" method="POST" data-ajax-form>
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
            @if($remainingBalance > 0 && $order->status !== 'cancelled')
                <form action="{{ route('admin.orders.payments.store', $order) }}" method="POST" class="mt-3" data-ajax-form data-tender-form>
                    @csrf
                    <label class="form-label">Record payment</label>
                    <div data-tender-list>
                        <div class="row g-2 mb-2" data-tender-row>
                            <div class="col-6"><select name="payments[0][payment_method]" class="form-select" required>@foreach(\App\Models\Order::PAYMENT_METHODS as $method)<option value="{{ $method }}">{{ strtoupper($method) }}</option>@endforeach</select></div>
                            <div class="col-6"><input name="payments[0][amount]" type="number" min="0.01" max="{{ number_format($remainingBalance, 2, '.', '') }}" step="0.01" value="{{ number_format($remainingBalance, 2, '.', '') }}" class="form-control" required></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2" data-add-tender>Add another tender</button>
                    <button class="btn btn-outline-success w-100" type="submit">Record payment</button>
                </form>
            @endif
            @if($refundableAmount > 0)
                <hr><h6 class="fw-bold">Record refund</h6>
                <form action="{{ route('admin.orders.refund', $order) }}" method="POST" data-ajax-form>
                    @csrf
                    <label class="form-label" for="refund_amount">Amount (up to {{ $order->currency_code }} {{ number_format($refundableAmount, 2) }})</label>
                    <input id="refund_amount" type="number" name="amount" min="0.01" max="{{ number_format($refundableAmount, 2, '.', '') }}" step="0.01" class="form-control mb-2" required>
                    <select name="payment_method" class="form-select mb-2" required>@foreach(\App\Models\Order::PAYMENT_METHODS as $method)<option value="{{ $method }}">{{ strtoupper($method) }}</option>@endforeach</select>
                    <input name="reference" class="form-control mb-2" maxlength="120" placeholder="Refund reference (optional)">
                    <textarea name="notes" class="form-control mb-2" rows="2" maxlength="500" required placeholder="Reason for refund"></textarea>
                    <button class="btn btn-outline-danger w-100" type="submit" data-confirm="Record this refund? This action cannot be undone.">Record refund</button>
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
