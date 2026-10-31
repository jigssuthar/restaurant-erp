@extends('layouts.admin')
@section('title', 'Stock History')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">Stock movement history</h3><p class="text-muted mb-0">{{ $inventory->name }} · {{ $inventory->branch->name }} · Current stock: {{ number_format((float) $inventory->quantity_on_hand, 3) }} {{ $inventory->unit }}</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('admin.inventory.index') }}">Back to inventory</a>
</div>
<div class="admin-table-card"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>DATE</th><th>TYPE</th><th>CHANGE</th><th>AFTER</th><th>REFERENCE / REASON</th><th>RECORDED BY</th></tr></thead><tbody>
@forelse($movements as $movement)
    <tr><td>{{ $movement->created_at->format('d M Y, H:i') }}</td><td>{{ ucfirst($movement->movement_type) }}</td><td>{{ $movement->movement_type === 'out' ? '-' : '+' }}{{ number_format((float) $movement->quantity, 3) }} {{ $inventory->unit }}</td><td>{{ number_format((float) $movement->quantity_after, 3) }} {{ $inventory->unit }}</td><td>{{ $movement->reason ?: '—' }}@if($movement->reference_type)<br><small class="text-muted">{{ str_replace('_', ' ', ucfirst($movement->reference_type)) }} #{{ $movement->reference_id }}</small>@endif</td><td>{{ $movement->creator?->name ?: 'System' }}</td></tr>
@empty<tr><td colspan="6" class="text-center text-muted py-5">No stock movements recorded.</td></tr>@endforelse
</tbody></table></div>{{ $movements->links() }}</div>
@endsection
