@extends('layouts.admin')
@section('title', $purchaseOrder->purchase_order_number)
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">{{ $purchaseOrder->purchase_order_number }}</h3><p class="text-muted mb-0">{{ $purchaseOrder->branch->name }} · {{ $purchaseOrder->supplier->name }}</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('admin.purchase-orders.index') }}">Back to purchase orders</a>
</div>
@include('admin.partials.feedback')
<div class="row g-4">
    <div class="col-lg-4"><div class="admin-card">
        <div class="d-flex justify-content-between"><strong>Status</strong><span class="badge bg-light text-dark">{{ ucfirst($purchaseOrder->status) }}</span></div>
        <hr><p><strong>Created</strong><br>{{ $purchaseOrder->created_at->format('d M Y, H:i') }}{{ $purchaseOrder->creator ? ' by '.$purchaseOrder->creator->name : '' }}</p>
        <p><strong>Expected delivery</strong><br>{{ $purchaseOrder->expected_on?->format('d M Y') ?: 'Not set' }}</p>
        @if($purchaseOrder->ordered_at)<p><strong>Ordered at</strong><br>{{ $purchaseOrder->ordered_at->format('d M Y, H:i') }}</p>@endif
        @if($purchaseOrder->received_at)<p><strong>Received at</strong><br>{{ $purchaseOrder->received_at->format('d M Y, H:i') }}</p>@endif
        @if($purchaseOrder->notes)<p class="mb-0"><strong>Notes</strong><br>{{ $purchaseOrder->notes }}</p>@endif
    </div>
    @if(in_array($purchaseOrder->status, ['draft', 'ordered']))
        <div class="admin-card"><h5 class="fw-bold mb-3">Update order</h5><form method="POST" action="{{ route('admin.purchase-orders.status', $purchaseOrder) }}" data-ajax-form>@csrf @method('PATCH')
            <label class="form-label" for="purchase-order-status">Next action</label><select class="form-select mb-3" id="purchase-order-status" name="status" required>
                @if($purchaseOrder->status === 'draft')<option value="ordered">Mark as ordered</option>@endif
                @if($purchaseOrder->status === 'ordered')<option value="received">Receive stock</option>@endif
                <option value="cancelled">Cancel purchase order</option>
            </select><button class="btn btn-orange w-100" data-confirm="{{ $purchaseOrder->status === 'ordered' ? 'Receive these quantities into inventory?' : 'Update this purchase order?' }}">Apply action</button>
        </form></div>
    @endif
    </div>
    <div class="col-lg-8"><div class="admin-table-card"><h5 class="fw-bold mb-3">Purchase order items</h5><div class="table-responsive"><table class="table align-middle"><thead><tr><th>ITEM</th><th>QTY</th><th>UNIT COST</th><th class="text-end">LINE TOTAL</th></tr></thead><tbody>
        @foreach($purchaseOrder->items as $line)<tr><td>{{ $line->item_name }}<br><small class="text-muted">{{ $line->unit }}</small></td><td>{{ number_format((float) $line->quantity, 3) }}</td><td>{{ $purchaseOrder->currency_code }} {{ number_format((float) $line->unit_cost, 2) }}</td><td class="text-end">{{ $purchaseOrder->currency_code }} {{ number_format((float) $line->line_total, 2) }}</td></tr>@endforeach
        </tbody><tfoot><tr><th colspan="3" class="text-end">Subtotal</th><th class="text-end">{{ $purchaseOrder->currency_code }} {{ number_format((float) $purchaseOrder->subtotal, 2) }}</th></tr></tfoot></table></div></div></div>
</div>
@endsection
