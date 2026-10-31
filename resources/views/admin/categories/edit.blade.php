@extends('layouts.admin')

@section('title', 'Edit Category')

@section('content')
<div class="mb-4"><h3 class="fw-bold mb-1">Edit Category</h3><p class="text-muted mb-0">Update category information.</p></div>
<div class="admin-card">
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.categories.partials.form')
    </form>
</div>
@endsection
