@extends('layouts.admin')
@section('title', 'Dining Tables')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">Dining Tables</h3><p class="text-muted mb-0">Manage seating by restaurant branch.</p></div>
    <a href="{{ route('admin.dining-tables.create') }}" class="btn btn-orange"><i class="bi bi-plus-lg me-2"></i>Add Table</a>
</div>
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="admin-table-card">
    <form method="GET" action="{{ route('admin.dining-tables.index') }}" class="row g-3 mb-4" data-ajax-filter>
        <div class="col-lg-4 col-md-6"><input type="search" name="search" class="form-control" placeholder="Search table or branch" value="{{ request('search') }}"></div>
        <div class="col-lg-4 col-md-6"><select name="branch_id" class="form-select">
            <option value="">All branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->organization->name }} — {{ $branch->name }}</option>
            @endforeach
        </select></div>
        <div class="col-lg-2 col-md-6"><select name="status" class="form-select">
            <option value="">All statuses</option>
            @foreach(['available', 'occupied', 'reserved', 'maintenance'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select></div>
        <div class="col-lg-2 col-md-6 d-flex gap-2"><button class="btn btn-orange" type="submit">Filter</button>
            <a href="{{ route('admin.dining-tables.index') }}" class="btn btn-light border">Reset</a></div>
    </form>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>TABLE</th><th>BRANCH</th><th>CAPACITY</th><th>STATUS</th><th class="text-end">ACTIONS</th></tr></thead>
        <tbody>
        @forelse($tables as $table)
            <tr>
                <td class="fw-bold">{{ $table->table_number }}</td>
                <td>{{ $table->branch->name }}<br><small class="text-muted">{{ $table->branch->organization->name }}</small></td>
                <td>{{ $table->capacity }} guests</td>
                <td><span class="badge
                    {{ $table->status === 'available' ? 'bg-success' : ($table->status === 'occupied' ? 'bg-danger' : ($table->status === 'reserved' ? 'bg-warning text-dark' : 'bg-secondary')) }}">
                    {{ ucfirst($table->status) }}</span></td>
                <td class="text-end"><div class="dropdown">
                    <button class="btn btn-light btn-sm" data-bs-toggle="dropdown" aria-label="Table actions"><i class="bi bi-three-dots-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.dining-tables.show', $table) }}">View</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.dining-tables.edit', $table) }}">Edit</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><form action="{{ route('admin.dining-tables.destroy', $table) }}" method="POST" data-ajax-form onsubmit="return confirm('Delete this table?')">
                            @csrf @method('DELETE')<button class="dropdown-item text-danger" type="submit">Delete</button></form></li>
                    </ul>
                </div></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-5">No dining tables found.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $tables->links() }}
</div>
@endsection
