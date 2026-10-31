@extends('layouts.admin')
@section('title', 'Suppliers')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4"><div><h3 class="fw-bold mb-1">Suppliers</h3><p class="text-muted mb-0">Manage supplier contacts for purchasing.</p></div><a class="btn btn-orange" href="{{ route('admin.suppliers.create') }}"><i class="bi bi-plus-lg me-2"></i>Add supplier</a></div>
@include('admin.partials.feedback')
<div class="admin-table-card"><form class="row g-2 mb-4" method="GET" action="{{ route('admin.suppliers.index') }}" data-ajax-filter><div class="col-md-9"><input class="form-control" type="search" name="search" placeholder="Search supplier, email, or phone" value="{{ request('search') }}"></div><div class="col-md-3"><button class="btn btn-outline-secondary w-100">Search</button></div></form>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>SUPPLIER</th><th>CONTACT</th><th>TAX REF.</th><th>ORDERS</th><th>STATUS</th><th></th></tr></thead><tbody>
    @forelse($suppliers as $supplier)<tr><td class="fw-semibold">{{ $supplier->name }}<br><small class="text-muted">{{ $supplier->address }}</small></td><td>{{ $supplier->email }}<br>{{ $supplier->phone }}</td><td>{{ $supplier->tax_reference ?: '—' }}</td><td>{{ $supplier->purchase_orders_count }}</td><td><span class="badge {{ $supplier->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.suppliers.edit', $supplier) }}">Edit</a><form class="d-inline" method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}" data-ajax-form>@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" data-confirm="Delete this supplier?">Delete</button></form></td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-5">No suppliers found.</td></tr>@endforelse
    </tbody></table></div>{{ $suppliers->links() }}
</div>
@endsection
