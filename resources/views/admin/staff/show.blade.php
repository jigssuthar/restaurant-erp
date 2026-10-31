@extends('layouts.admin')
@section('title', $staff->name)
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">{{ $staff->name }}</h3><p class="text-muted mb-0">{{ $staff->email }}</p></div>
    <div class="d-flex gap-2"><a href="{{ route('admin.staff.edit', $staff) }}" class="btn btn-orange">Edit Account</a>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-light border">Back</a></div>
</div>
<div class="admin-card"><div class="row g-4">
    <div class="col-md-6"><strong>Role</strong><div>{{ ucfirst($staff->role) }}</div></div>
    <div class="col-md-6"><strong>Organization</strong><div>{{ $staff->organization?->name ?? '—' }}</div></div>
    <div class="col-md-6"><strong>Branch</strong><div>{{ $staff->branch?->name ?? '—' }}</div></div>
    <div class="col-md-6"><strong>Joined</strong><div>{{ $staff->created_at->format('d M Y') }}</div></div>
</div></div>
@endsection
