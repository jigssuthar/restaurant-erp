@extends('layouts.operations')
@section('title', 'Kitchen Display')

@push('styles')
<style>
    .kds-toolbar{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1.25rem}
    .kds-board{display:grid;grid-template-columns:repeat(4,minmax(230px,1fr));gap:1rem;align-items:start}
    .kds-lane{background:#eef2f6;border-radius:14px;padding:.85rem;min-height:60vh}
    .kds-lane-head{display:flex;align-items:center;justify-content:space-between;font-weight:700;margin-bottom:.75rem}
    .kds-count{background:#fff;border-radius:999px;padding:.15rem .6rem;font-size:.8rem}
    .kds-ticket{background:#fff;border:1px solid #e2e8f0;border-left:4px solid #f97316;border-radius:12px;padding:.9rem;margin-bottom:.75rem;box-shadow:0 3px 12px #1720330b}
    .kds-ticket[data-status=preparing]{border-left-color:#3b82f6}.kds-ticket[data-status=ready]{border-left-color:#16a34a}.kds-ticket[data-status=completed]{border-left-color:#94a3b8}
    .kds-items{list-style:none;padding:0;margin:.75rem 0}.kds-items li{border-top:1px solid #f1f5f9;padding:.45rem 0;font-size:.93rem}
    @media(max-width:1100px){.kds-board{grid-template-columns:repeat(2,minmax(230px,1fr))}}
    @media(max-width:620px){.kds-toolbar{align-items:flex-start;flex-direction:column}.kds-board{grid-template-columns:1fr}.kds-lane{min-height:unset}}
</style>
@endpush

@section('content')
<div class="kds-toolbar">
    <div>
        <h1 class="h3 fw-bold mb-1">Kitchen Display</h1>
        <p class="text-muted mb-0">Live order tickets, from new to ready and served.</p>
    </div>
    <form method="GET" action="{{ route('kitchen-display.index') }}" data-ajax-filter class="d-flex align-items-center gap-2">
        @if($branches->count() > 1)
            <label class="visually-hidden" for="kdsBranch">Branch</label>
            <select id="kdsBranch" name="branch_id" class="form-select">
                @foreach($branches as $option)
                    <option value="{{ $option->id }}" @selected($branch?->id === $option->id)>{{ $option->name }}</option>
                @endforeach
            </select>
        @endif
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-board-refresh><i class="bi bi-arrow-clockwise"></i><span class="visually-hidden">Refresh kitchen board</span></button>
            <span class="small text-muted text-nowrap">Auto refresh: 10 sec</span>
        </div>
    </form>
</div>
<div data-operations-board data-feed-url="{{ route('kitchen-display.feed', array_filter(['branch_id' => $branch?->id])) }}">
    @include('admin.kitchen-display.partials.board')
</div>
@endsection
