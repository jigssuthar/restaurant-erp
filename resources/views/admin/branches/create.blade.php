@extends('layouts.admin')

@section('title', 'Add Branch')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Add Branch</h3>
    <p class="text-muted mb-0">Create a new location for an organization.</p>
</div>
<div class="admin-card">
    <form action="{{ route('admin.branches.store') }}" method="POST">
        @csrf
        @include('admin.branches.partials.form')
    </form>
</div>
@endsection
