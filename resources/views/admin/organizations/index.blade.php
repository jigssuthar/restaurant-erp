
@extends('layouts.admin')

@section('title', 'Organizations')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

    <div>
        <h3 class="fw-bold mb-1">Organizations</h3>
        <p class="text-muted mb-0">Manage all restaurant businesses and companies.</p>
    </div>

    <a href="{{ route('admin.organizations.create') }}" class="btn btn-orange">
        <i class="bi bi-plus-lg me-2"></i>Add Organization
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

<div class="row g-3 mb-4">

    <div class="col-md-4">
        <div class="admin-card">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="text-muted mb-1">Total Organizations</p>
                    <h3 class="fw-bold mb-0">{{ $organizations->total() }}</h3>
                </div>
                <div class="stat-icon orange-bg">
                    <i class="bi bi-building"></i>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="admin-table-card">

    <form method="GET" class="row g-3 mb-4">

        <div class="col-lg-5 col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>

                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Search organization..."
                       value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-lg-3 col-md-6">

            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="active" @selected(request('status') == 'active')>Active</option>
                <option value="inactive" @selected(request('status') == 'inactive')>Inactive</option>
            </select>

        </div>

        <div class="col-lg-4 d-flex gap-2">

            <button type="submit" class="btn btn-orange">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>

            <a href="{{ route('admin.organizations.index') }}"
               class="btn btn-light border">
                Reset
            </a>

        </div>

    </form>

    <div class="table-responsive">

        <table class="table align-middle organization-table">

            <thead>
                <tr>
                    <th>ORGANIZATION</th>
                    <th>CONTACT DETAILS</th>
                    <th>GST NUMBER</th>
                    <th>BRANCHES</th>
                    <th>SUBSCRIPTION</th>
                    <th>STATUS</th>
                    <th class="text-end">ACTION</th>
                </tr>
            </thead>

            <tbody>

                @forelse($organizations as $organization)

                <tr>

                    <td>
                        <div class="d-flex align-items-center gap-3">

                            @if($organization->logo)
                                <img src="{{ asset('storage/'.$organization->logo) }}"
                                     class="organization-logo"
                                     alt="Logo">
                            @else
                                <div class="organization-logo-placeholder">
                                    <i class="bi bi-building"></i>
                                </div>
                            @endif

                            <div>
                                <div class="fw-bold">{{ $organization->name }}</div>
                                <small class="text-muted">{{ $organization->slug }}</small>
                            </div>

                        </div>
                    </td>

                    <td>
                        <div>{{ $organization->email ?? '-' }}</div>
                        <small class="text-muted">{{ $organization->phone ?? '-' }}</small>
                    </td>

                    <td>{{ $organization->gst_number ?? '-' }}</td>

                    <td>
                        <span class="branch-count">
                            {{ $organization->branches_count ?? 0 }}
                        </span>
                    </td>

                    <td>
                        @if($organization->currentSubscription)
                            <span class="badge text-bg-light">{{ ucfirst(str_replace('_', ' ', $organization->currentSubscription->status)) }}</span>
                            <div class="small text-muted">{{ $organization->currentSubscription->plan?->name ?? 'Free trial' }}</div>
                        @else
                            <span class="text-muted">No subscription</span>
                        @endif
                    </td>

                    <td>
                        <span class="status-badge {{ $organization->status == 'active' ? 'status-active' : 'status-inactive' }}">
                            {{ ucfirst($organization->status) }}
                        </span>
                    </td>

                    <td class="text-end">

                        <div class="dropdown">

                            <button class="btn btn-light btn-sm"
                                    data-bs-toggle="dropdown"
                                    aria-label="Organization actions">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end">

                                <li>
                                    <a class="dropdown-item"
                                       href="{{ route('admin.organizations.show', $organization) }}">
                                        <i class="bi bi-eye me-2"></i>View
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                       href="{{ route('admin.organizations.edit', $organization) }}">
                                        <i class="bi bi-pencil me-2"></i>Edit
                                    </a>
                                </li>

                                <li><hr class="dropdown-divider"></li>

                                <li>
                                    <form action="{{ route('admin.organizations.destroy', $organization) }}"
                                          method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this organization?')">
                                        @csrf
                                        @method('DELETE')

                                        <button class="dropdown-item text-danger">
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
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-building fs-1 text-muted"></i>
                        <h6 class="mt-3">No Organizations Found</h6>
                        <p class="text-muted">Start by adding your first organization.</p>
                        <a href="{{ route('admin.organizations.create') }}"
                           class="btn btn-orange btn-sm">
                            Add Organization
                        </a>
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <small class="text-muted">
            Showing {{ $organizations->firstItem() ?? 0 }}
            to {{ $organizations->lastItem() ?? 0 }}
            of {{ $organizations->total() }} organizations
        </small>

        {{ $organizations->links() }}
    </div>

</div>

@endsection

@push('styles')
<style>
.organization-table thead th {
    font-size: 11px;
    color: #64748b;
    font-weight: 700;
    background: #f8fafc;
    padding: 16px;
    white-space: nowrap;
}

.organization-table tbody td {
    padding: 17px 16px;
    font-size: 13px;
}

.organization-logo,
.organization-logo-placeholder {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
}

.organization-logo-placeholder {
    background: #fff1e7;
    color: #f97316;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.branch-count {
    background: #eff6ff;
    color: #2563eb;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 700;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.status-active {
    color: #15803d;
    background: #dcfce7;
}

.status-inactive {
    color: #64748b;
    background: #f1f5f9;
}

@media(max-width:767px) {
    .organization-table tbody td {
        padding: 13px 10px;
    }
}
</style>
@endpush
