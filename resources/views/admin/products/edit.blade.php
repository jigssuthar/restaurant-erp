@extends('layouts.admin')
@section('title', 'Edit Product')
@section('content')
<div class="mb-4"><h3 class="fw-bold mb-1">Edit Product</h3><p class="text-muted mb-0">Update menu item details.</p></div>
<div class="admin-card"><form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('admin.products.partials.form')
</form></div>
@endsection
