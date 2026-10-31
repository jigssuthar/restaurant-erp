@extends('layouts.admin')
@section('title', 'Add Dining Table')
@section('content')
<div class="mb-4"><h3 class="fw-bold mb-1">Add Dining Table</h3><p class="text-muted mb-0">Add a table to a branch.</p></div>
<div class="admin-card"><form action="{{ route('admin.dining-tables.store') }}" method="POST">
    @csrf @include('admin.dining-tables.partials.form')
</form></div>
@endsection
