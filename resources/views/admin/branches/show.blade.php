@extends('layouts.admin')

@section('title', $branch->name)

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">{{ $branch->name }}</h3>
        <p class="text-muted mb-0">{{ $branch->organization->name }} · {{ $branch->branch_code }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-orange">
            <i class="bi bi-pencil me-1"></i>Edit Branch
        </a>
        <a href="{{ route('admin.branches.index') }}" class="btn btn-light border">Back</a>
    </div>
</div>

<div class="admin-card">
    <div class="row g-4">
        <div class="col-md-6"><strong>Organization</strong><div>{{ $branch->organization->name }}</div></div>
        <div class="col-md-6"><strong>Status</strong><div>{{ ucfirst($branch->status) }}</div></div>
        <div class="col-md-6"><strong>Email</strong><div>{{ $branch->email ?: '-' }}</div></div>
        <div class="col-md-6"><strong>Phone</strong><div>{{ $branch->phone ?: '-' }}</div></div>
        <div class="col-md-6"><strong>Address</strong><div>{{ $branch->address ?: '-' }}</div></div>
        <div class="col-md-6"><strong>City / State / Pincode</strong>
            <div>{{ collect([$branch->city, $branch->state, $branch->pincode])->filter()->join(', ') ?: '-' }}</div>
        </div>
    </div>
</div>

<div class="row g-3">
    @foreach([
        ['Staff', $branch->users_count, 'bi-people'],
        ['Categories', $branch->categories_count, 'bi-grid-3x3'],
        ['Products', $branch->products_count, 'bi-cup-hot'],
        ['Dining tables', $branch->dining_tables_count, 'bi-table'],
    ] as [$label, $count, $icon])
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div><p class="text-muted mb-1">{{ $label }}</p><h4 class="mb-0">{{ $count }}</h4></div>
                    <i class="bi {{ $icon }} fs-3 text-warning"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
