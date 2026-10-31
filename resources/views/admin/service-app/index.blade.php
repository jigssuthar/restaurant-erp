@extends('layouts.operations')
@section('title', 'Service App')

@push('styles')
<style>
    .service-toolbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1rem}
    .service-table-card{height:100%;border:1px solid #e2e8f0;border-radius:14px;background:#fff;padding:1rem;box-shadow:0 3px 14px #1720330b}
    .service-table-card[data-state=occupied]{border-top:4px solid #f97316}.service-table-card[data-state=available]{border-top:4px solid #16a34a}
    .service-request{border:1px solid #f1f5f9;border-radius:10px;padding:.7rem;margin-top:.65rem}
    .service-order{border-top:1px solid #eef2f6;padding-top:.75rem;margin-top:.75rem}
    @media(max-width:620px){.service-toolbar{align-items:stretch;flex-direction:column}}
</style>
@endpush

@section('content')
<div class="service-toolbar">
    <div>
        <h1 class="h3 fw-bold mb-1">Service App</h1>
        <p class="text-muted mb-0">Manage tables, guest requests, serving and bill payments.</p>
    </div>
    <form method="GET" action="{{ route('service-app.index') }}" data-ajax-filter class="d-flex align-items-center gap-2">
        @if($branches->count() > 1)
            <label class="visually-hidden" for="serviceBranch">Branch</label>
            <select id="serviceBranch" name="branch_id" class="form-select">
                @foreach($branches as $option)
                    <option value="{{ $option->id }}" @selected($branch?->id === $option->id)>{{ $option->name }}</option>
                @endforeach
            </select>
        @endif
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-board-refresh><i class="bi bi-arrow-clockwise"></i><span class="visually-hidden">Refresh service board</span></button>
            <span class="small text-muted text-nowrap">Auto refresh: 10 sec</span>
        </div>
    </form>
</div>
<div data-operations-board data-feed-url="{{ route('service-app.feed', array_filter(['branch_id' => $branch?->id])) }}">
    @include('admin.service-app.partials.board')
</div>
@endsection
