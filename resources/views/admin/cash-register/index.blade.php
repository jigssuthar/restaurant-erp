@extends('layouts.admin')
@section('title', 'Cash Register')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">Cash Register & Day Close</h3><p class="text-muted mb-0">Open a till, link cash tenders to its session, and reconcile cash at closing.</p></div>
    <form method="GET" action="{{ route('admin.cash-register.index') }}" class="d-flex gap-2" data-ajax-filter>
        <select class="form-select" name="branch_id" aria-label="Branch">
            @foreach($branches as $option)<option value="{{ $option->id }}" @selected($branch?->id === $option->id)>{{ $option->name }}</option>@endforeach
        </select>
        <button class="btn btn-outline-secondary">Select</button>
    </form>
</div>
@include('admin.partials.feedback')
@if(!$branch)
    <div class="alert alert-warning">No branch is available for this account.</div>
@elseif($activeSession)
    @php
        $sessionCashIn = (float) $activeSession->payments()->where('payment_method', 'cash')->where('transaction_type', 'payment')->sum('amount');
        $sessionCashOut = (float) $activeSession->payments()->where('payment_method', 'cash')->where('transaction_type', 'refund')->sum('amount');
        $expectedNow = (float) $activeSession->opening_float + $sessionCashIn - $sessionCashOut;
    @endphp
    <div class="row g-4 mb-4">
        <div class="col-lg-7"><div class="admin-card h-100">
            <div class="d-flex justify-content-between align-items-start"><div><h5 class="fw-bold mb-1">Register open</h5><p class="text-muted">Opened {{ $activeSession->opened_at->format('d M Y, H:i') }} · {{ $branch->name }}</p></div><span class="badge bg-success">Open</span></div>
            <div class="row g-3">
                <div class="col-6"><small class="text-muted">Opening float</small><div class="fw-bold">{{ $branch->organization->settings?->currency_code ?? 'INR' }} {{ number_format((float) $activeSession->opening_float, 2) }}</div></div>
                <div class="col-6"><small class="text-muted">Cash collected</small><div class="fw-bold">{{ $branch->organization->settings?->currency_code ?? 'INR' }} {{ number_format($sessionCashIn, 2) }}</div></div>
                <div class="col-6"><small class="text-muted">Cash refunds</small><div class="fw-bold">{{ $branch->organization->settings?->currency_code ?? 'INR' }} {{ number_format($sessionCashOut, 2) }}</div></div>
                <div class="col-6"><small class="text-muted">Expected cash now</small><div class="fw-bold fs-5">{{ $branch->organization->settings?->currency_code ?? 'INR' }} {{ number_format($expectedNow, 2) }}</div></div>
            </div>
        </div></div>
        <div class="col-lg-5"><div class="admin-card">
            <h5 class="fw-bold mb-3">Close register</h5>
            <form method="POST" action="{{ route('admin.cash-register.close', $activeSession) }}" data-ajax-form>
                @csrf @method('PATCH')
                <label class="form-label" for="counted_cash">Counted cash ({{ $branch->organization->settings?->currency_code ?? 'INR' }})</label>
                <input class="form-control mb-3" id="counted_cash" name="counted_cash" type="number" min="0" step="0.01" required>
                <label class="form-label" for="closing_notes">Closing notes</label>
                <textarea class="form-control mb-3" id="closing_notes" name="closing_notes" rows="2" maxlength="2000"></textarea>
                <button class="btn btn-orange w-100" type="submit" data-confirm="Close this register? New cash payments will no longer be linked to this session.">Close and reconcile</button>
            </form>
        </div></div>
    </div>
@else
    <div class="admin-card mb-4">
        <h5 class="fw-bold mb-3">Open register · {{ $branch->name }}</h5>
        <form class="row g-3 align-items-end" method="POST" action="{{ route('admin.cash-register.open') }}" data-ajax-form>
            @csrf<input type="hidden" name="branch_id" value="{{ $branch->id }}">
            <div class="col-md-6"><label class="form-label" for="opening_float">Opening cash float ({{ $branch->organization->settings?->currency_code ?? 'INR' }})</label><input id="opening_float" class="form-control" type="number" min="0" step="0.01" name="opening_float" value="0" required></div>
            <div class="col-md-6"><button class="btn btn-orange">Open register</button></div>
        </form>
    </div>
@endif
<div class="admin-table-card">
    <h5 class="fw-bold mb-3">Register sessions · {{ $branch?->name }}</h5>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>OPENED</th><th>OPENED BY</th><th>CLOSED</th><th>OPENING FLOAT</th><th>EXPECTED</th><th>COUNTED</th><th>DIFFERENCE</th><th>STATUS</th></tr></thead><tbody>
        @forelse($sessions as $session)
            <tr><td>{{ $session->opened_at->format('d M Y, H:i') }}</td><td>{{ $session->opener->name }}</td><td>{{ $session->closed_at?->format('d M Y, H:i') ?: '—' }}@if($session->closer)<br><small>{{ $session->closer->name }}</small>@endif</td><td>{{ number_format((float) $session->opening_float, 2) }}</td><td>{{ $session->expected_cash === null ? '—' : number_format((float) $session->expected_cash, 2) }}</td><td>{{ $session->counted_cash === null ? '—' : number_format((float) $session->counted_cash, 2) }}</td><td>{{ $session->cash_difference === null ? '—' : number_format((float) $session->cash_difference, 2) }}</td><td><span class="badge {{ $session->closed_at ? 'bg-secondary' : 'bg-success' }}">{{ $session->closed_at ? 'Closed' : 'Open' }}</span></td></tr>
        @empty<tr><td colspan="8" class="text-center text-muted py-4">No register sessions yet.</td></tr>@endforelse
    </tbody></table></div>{{ $sessions->links() }}
</div>
@endsection
