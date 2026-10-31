@extends('layouts.admin')
@section('title', 'Add Account')
@section('content')
<div class="mb-4"><h3 class="fw-bold mb-1">Add Account</h3><p class="text-muted mb-0">Create a staff or management account.</p></div>
<div class="admin-card"><form action="{{ route('admin.staff.store') }}" method="POST">
    @csrf @include('admin.staff.partials.form')
</form></div>
@endsection
