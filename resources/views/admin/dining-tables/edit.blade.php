@extends('layouts.admin')
@section('title', 'Edit Dining Table')
@section('content')
<div class="mb-4"><h3 class="fw-bold mb-1">Edit Dining Table</h3><p class="text-muted mb-0">Update seating details.</p></div>
<div class="admin-card"><form action="{{ route('admin.dining-tables.update', $table) }}" method="POST">
    @csrf @method('PUT') @include('admin.dining-tables.partials.form')
</form></div>
@endsection
