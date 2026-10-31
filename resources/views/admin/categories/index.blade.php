@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Categories</h3>
        <p class="text-muted mb-0">Organize menu items by branch and category.</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-orange">
        <i class="bi bi-plus-lg me-2"></i>Add Category
    </a>
</div>
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="admin-table-card">
    <form method="GET" class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <input type="search" name="search" class="form-control" placeholder="Search category or branch"
                   value="{{ request('search') }}">
        </div>
        <div class="col-lg-3 col-md-6">
            <select name="branch_id" class="form-select">
                <option value="">All branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>
                        {{ $branch->organization->name }} — {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-6">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="col-lg-3 col-md-6 d-flex gap-2">
            <button type="submit" class="btn btn-orange"><i class="bi bi-funnel me-1"></i>Filter</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-light border">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>CATEGORY</th><th>BRANCH</th><th>PRODUCTS</th><th>STATUS</th><th class="text-end">ACTIONS</th></tr></thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @if($category->image)
                                    <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}"
                                         style="width:44px;height:44px;object-fit:cover;border-radius:8px">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-light rounded"
                                         style="width:44px;height:44px"><i class="bi bi-grid-3x3 text-muted"></i></div>
                                @endif
                                <div><div class="fw-bold">{{ $category->name }}</div>
                                    <small class="text-muted">{{ $category->slug }}</small></div>
                            </div>
                        </td>
                        <td>{{ $category->branch->name }}<br><small class="text-muted">{{ $category->branch->organization->name }}</small></td>
                        <td>{{ $category->products_count }}</td>
                        <td><span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" data-bs-toggle="dropdown" aria-label="Category actions">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('admin.categories.show', $category) }}">View</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.categories.edit', $category) }}">Edit</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                                              onsubmit="return confirm('Delete this category?')">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger" type="submit">Delete</button>
                                    </form></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $categories->links() }}
</div>
@endsection
