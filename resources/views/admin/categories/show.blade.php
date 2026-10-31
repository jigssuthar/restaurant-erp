@extends('layouts.admin')

@section('title', $category->name)

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">{{ $category->name }}</h3>
        <p class="text-muted mb-0">{{ $category->branch->organization->name }} — {{ $category->branch->name }}</p></div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-orange">Edit Category</a>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-light border">Back</a>
    </div>
</div>
<div class="admin-card">
    <div class="row g-4">
        @if($category->image)
            <div class="col-12"><img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}"
                                     class="rounded" style="max-width:240px"></div>
        @endif
        <div class="col-md-6"><strong>Status</strong><div>{{ $category->is_active ? 'Active' : 'Inactive' }}</div></div>
        <div class="col-md-6"><strong>Products</strong><div>{{ $category->products_count }}</div></div>
        <div class="col-12"><strong>Description</strong><div>{{ $category->description ?: '-' }}</div></div>
    </div>
</div>
@endsection
