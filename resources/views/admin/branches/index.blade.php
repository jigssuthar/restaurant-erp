@extends('layouts.admin')

@section('title', 'Branches')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Branches</h3>
        <p class="text-muted mb-0">Manage organization locations and branch details.</p>
    </div>
    <a href="{{ route('admin.branches.create') }}" class="btn btn-orange">
        <i class="bi bi-plus-lg me-2"></i>Add Branch
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="admin-table-card">
    <form method="GET" class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <input type="search" name="search" class="form-control"
                   placeholder="Search branch, code, city, or organization"
                   value="{{ request('search') }}">
        </div>
        <div class="col-lg-3 col-md-6">
            <select name="organization_id" class="form-select">
                <option value="">All organizations</option>
                @foreach($organizations as $organization)
                    <option value="{{ $organization->id }}"
                        @selected((string) request('organization_id') === (string) $organization->id)>
                        {{ $organization->name }}
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
            <button type="submit" class="btn btn-orange">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            <a href="{{ route('admin.branches.index') }}" class="btn btn-light border">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>BRANCH</th>
                    <th>ORGANIZATION</th>
                    <th>CONTACT</th>
                    <th>STAFF</th>
                    <th>STATUS</th>
                    <th class="text-end">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $branch->name }}</div>
                            <small class="text-muted">{{ $branch->branch_code }}</small>
                        </td>
                        <td>{{ $branch->organization->name }}</td>
                        <td>
                            <div>{{ $branch->email ?: '-' }}</div>
                            <small class="text-muted">{{ $branch->phone ?: '-' }}</small>
                        </td>
                        <td>{{ $branch->users_count }}</td>
                        <td>
                            <span class="badge {{ $branch->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($branch->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" data-bs-toggle="dropdown"
                                        aria-label="Branch actions">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('admin.branches.show', $branch) }}">
                                        <i class="bi bi-eye me-2"></i>View
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.branches.edit', $branch) }}">
                                        <i class="bi bi-pencil me-2"></i>Edit
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST"
                                              onsubmit="return confirm('Delete this branch?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            No branches found. Add a branch to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $branches->links() }}
</div>
@endsection
