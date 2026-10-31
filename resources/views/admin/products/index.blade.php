@extends('layouts.admin')

@section('title', 'Products & Menu')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">Products & Menu</h3><p class="text-muted mb-0">Manage menu items, prices, and availability.</p></div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-orange"><i class="bi bi-plus-lg me-2"></i>Add Product</a>
</div>
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
<div class="admin-table-card">
    <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3 mb-4" data-ajax-filter>
        <div class="col-lg-5 col-md-6"><input type="search" name="search" class="form-control"
            placeholder="Search product or SKU" value="{{ request('search') }}"></div>
        <div class="col-lg-3 col-md-6"><select name="branch_id" class="form-select">
            <option value="">All branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>
                    {{ $branch->organization->name }} — {{ $branch->name }}
                </option>
            @endforeach
        </select></div>
        <div class="col-lg-2 col-md-6"><select name="status" class="form-select">
            <option value="">All availability</option>
            <option value="available" @selected(request('status') === 'available')>Available</option>
            <option value="unavailable" @selected(request('status') === 'unavailable')>Unavailable</option>
        </select></div>
        <div class="col-lg-2 col-md-6 d-flex gap-2">
            <button type="submit" class="btn btn-orange">Filter</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-light border">Reset</a>
        </div>
    </form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>PRODUCT</th><th>CATEGORY</th><th>BRANCH</th><th>PRICE</th><th>AVAILABILITY</th><th class="text-end">ACTIONS</th></tr></thead>
        <tbody>
        @forelse($products as $product)
            <tr>
                <td><div class="d-flex align-items-center gap-3">
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}"
                             style="width:44px;height:44px;object-fit:cover;border-radius:8px">
                    @else
                        <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width:44px;height:44px">
                            <i class="bi bi-cup-hot text-muted"></i></div>
                    @endif
                    <div><div class="fw-bold">{{ $product->name }}</div><small class="text-muted">{{ $product->sku ?: 'No SKU' }}</small></div>
                </div></td>
                <td>{{ $product->category->name }}</td>
                <td>{{ $product->branch->name }}<br><small class="text-muted">{{ $product->branch->organization->name }}</small></td>
                <td>{{ $product->branch->organization->settings?->currency_code ?? 'INR' }} {{ number_format((float) $product->price, 2) }}</td>
                <td><span class="badge {{ $product->is_available ? 'bg-success' : 'bg-secondary' }}">
                    {{ $product->is_available ? 'Available' : 'Unavailable' }}</span></td>
                <td class="text-end"><div class="dropdown">
                    <button class="btn btn-light btn-sm" data-bs-toggle="dropdown" aria-label="Product actions"><i class="bi bi-three-dots-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.products.show', $product) }}">View</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.products.edit', $product) }}">Edit</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><form action="{{ route('admin.products.destroy', $product) }}" method="POST" data-ajax-form onsubmit="return confirm('Delete this product?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                        </form></li>
                    </ul>
                </div></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">No products found.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $products->links() }}
</div>
@endsection
