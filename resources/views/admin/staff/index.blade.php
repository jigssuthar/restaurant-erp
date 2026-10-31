@extends('layouts.admin')
@section('title', 'Staff Management')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">Staff Management</h3><p class="text-muted mb-0">Manage account access, roles, and branch assignments.</p></div>
    <a href="{{ route('admin.staff.create') }}" class="btn btn-orange"><i class="bi bi-person-plus me-2"></i>Add Account</a>
</div>
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="admin-table-card">
    <form method="GET" class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6"><input type="search" name="search" class="form-control" placeholder="Search name or email" value="{{ request('search') }}"></div>
        <div class="col-lg-3 col-md-6"><select name="role" class="form-select">
            <option value="">All roles</option>
            @foreach($roles as $role)<option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>@endforeach
        </select></div>
        <div class="col-lg-3 col-md-6"><select name="organization_id" class="form-select">
            <option value="">All organizations</option>
            @foreach($organizations as $organization)
                <option value="{{ $organization->id }}" @selected((string) request('organization_id') === (string) $organization->id)>{{ $organization->name }}</option>
            @endforeach
        </select></div>
        <div class="col-lg-2 col-md-6 d-flex gap-2"><button type="submit" class="btn btn-orange">Filter</button>
            <a href="{{ route('admin.staff.index') }}" class="btn btn-light border">Reset</a></div>
    </form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>ACCOUNT</th><th>ROLE</th><th>ORGANIZATION</th><th>BRANCH</th><th class="text-end">ACTIONS</th></tr></thead>
        <tbody>
        @forelse($staff as $account)
            <tr>
                <td><div class="fw-bold">{{ $account->name }}</div><small class="text-muted">{{ $account->email }}</small></td>
                <td><span class="badge {{ $account->role === 'admin' ? 'bg-danger' : ($account->role === 'manager' ? 'bg-primary' : 'bg-secondary') }}">{{ ucfirst($account->role) }}</span></td>
                <td>{{ $account->organization?->name ?? '—' }}</td>
                <td>{{ $account->branch?->name ?? '—' }}</td>
                <td class="text-end"><div class="dropdown">
                    <button class="btn btn-light btn-sm" data-bs-toggle="dropdown" aria-label="Account actions"><i class="bi bi-three-dots-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.staff.show', $account) }}">View</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.staff.edit', $account) }}">Edit</a></li>
                        @unless($account->is(auth()->user()))
                            <li><hr class="dropdown-divider"></li>
                            <li><form action="{{ route('admin.staff.destroy', $account) }}" method="POST" onsubmit="return confirm('Delete this account?')">
                                @csrf @method('DELETE')<button class="dropdown-item text-danger" type="submit">Delete</button></form></li>
                        @endunless
                    </ul>
                </div></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-5">No accounts found.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $staff->links() }}
</div>
@endsection
