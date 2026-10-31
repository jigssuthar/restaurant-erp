@extends('layouts.admin')
@section('title', 'Add Product')
@section('content')
<div class="mb-4"><h3 class="fw-bold mb-1">Add Product</h3><p class="text-muted mb-0">Add a menu item to a branch.</p></div>
<div class="admin-card"><form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.products.partials.form')
</form></div>
@endsection
