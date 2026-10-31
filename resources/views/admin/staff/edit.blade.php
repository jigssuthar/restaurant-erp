@extends('layouts.admin')
@section('title', 'Edit Account')
@section('content')
<div class="mb-4"><h3 class="fw-bold mb-1">Edit Account</h3><p class="text-muted mb-0">Update account role and assignments.</p></div>
<div class="admin-card"><form action="{{ route('admin.staff.update', $staff) }}" method="POST">
    @csrf @method('PUT') @include('admin.staff.partials.form')
</form></div>
@endsection
