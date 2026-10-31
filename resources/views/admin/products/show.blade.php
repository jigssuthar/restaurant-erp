@extends('layouts.admin')
@section('title', $product->name)
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">{{ $product->name }}</h3>
        <p class="text-muted mb-0">{{ $product->branch->organization->name }} — {{ $product->branch->name }}</p></div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-orange">Edit Product</a>
        <a href="{{ route('admin.products.index') }}" class="btn btn-light border">Back</a>
    </div>
</div>
<div class="admin-card">
    <div class="row g-4">
        @if($product->image)
            <div class="col-12"><img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="rounded" style="max-width:240px"></div>
        @endif
        <div class="col-md-6"><strong>Category</strong><div>{{ $product->category->name }}</div></div>
        <div class="col-md-6"><strong>SKU</strong><div>{{ $product->sku ?: '-' }}</div></div>
        <div class="col-md-6"><strong>Price</strong><div>{{ $product->branch->organization->settings?->currency_code ?? 'INR' }} {{ number_format((float) $product->price, 2) }}</div></div>
        <div class="col-md-6"><strong>Cost price</strong><div>{{ $product->branch->organization->settings?->currency_code ?? 'INR' }} {{ number_format((float) $product->cost_price, 2) }}</div></div>
        <div class="col-md-6"><strong>Type</strong><div>{{ ucfirst($product->type) }}</div></div>
        <div class="col-md-6"><strong>Availability</strong><div>{{ $product->is_available ? 'Available' : 'Unavailable' }}</div></div>
        <div class="col-12"><strong>Description</strong><div>{{ $product->description ?: '-' }}</div></div>
    </div>
</div>
@endsection
