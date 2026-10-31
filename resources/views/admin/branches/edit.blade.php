@extends('layouts.admin')

@section('title', 'Edit Branch')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Edit Branch</h3>
    <p class="text-muted mb-0">Update branch information.</p>
</div>
<div class="admin-card">
    <form action="{{ route('admin.branches.update', $branch) }}" method="POST" data-ajax-form>
        @csrf
        @method('PUT')
        @include('admin.branches.partials.form')
    </form>
</div>
@endsection
