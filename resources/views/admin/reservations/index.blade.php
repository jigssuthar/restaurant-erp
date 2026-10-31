@extends('layouts.admin')
@section('title', 'Reservations')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4"><div><h3 class="fw-bold mb-1">Reservations</h3><p class="text-muted mb-0">Manage guest bookings and table schedules.</p></div><a class="btn btn-orange" href="{{ route('admin.reservations.create') }}"><i class="bi bi-plus-lg me-2"></i>New reservation</a></div>
@include('admin.partials.feedback')
<div class="admin-table-card">
    <form class="row g-2 mb-4" method="GET" action="{{ route('admin.reservations.index') }}" data-ajax-filter>
        <div class="col-lg-3"><input class="form-control" type="search" name="search" placeholder="Guest, phone, booking #" value="{{ request('search') }}"></div>
        <div class="col-lg-2"><input class="form-control" type="date" name="date" value="{{ request('date') }}"></div>
        <div class="col-lg-2"><select class="form-select" name="branch_id"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
        <div class="col-lg-2"><select class="form-select" name="status"><option value="">All statuses</option>@foreach(\App\Models\Reservation::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>@endforeach</select></div>
        <div class="col-lg-3"><button class="btn btn-outline-secondary w-100">Filter</button></div>
    </form>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>BOOKING</th><th>GUEST</th><th>BRANCH / TABLE</th><th>PARTY</th><th>DATE / TIME</th><th>STATUS</th><th></th></tr></thead><tbody>
    @forelse($reservations as $reservation)
        <tr><td class="fw-semibold">{{ $reservation->reservation_number }}</td><td>{{ $reservation->customer_name }}<br><small class="text-muted">{{ $reservation->customer_phone }}</small></td><td>{{ $reservation->branch->name }}<br><small>{{ $reservation->diningTable?->table_number ? 'Table '.$reservation->diningTable->table_number : 'Unassigned' }}</small></td><td>{{ $reservation->party_size }}</td><td>{{ $reservation->starts_at->format('d M Y, H:i') }}<br><small>until {{ $reservation->ends_at->format('H:i') }}</small></td><td><span class="badge bg-light text-dark">{{ ucfirst(str_replace('_',' ',$reservation->status)) }}</span></td><td><form method="POST" action="{{ route('admin.reservations.status', $reservation) }}" data-ajax-form class="d-flex gap-1">@csrf @method('PATCH')<select class="form-select form-select-sm" name="status">@foreach(\App\Models\Reservation::STATUSES as $status)<option value="{{ $status }}" @selected($reservation->status === $status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>@endforeach</select><button class="btn btn-sm btn-outline-primary">Save</button></form></td></tr>
    @empty<tr><td colspan="7" class="text-center text-muted py-5">No reservations found.</td></tr>@endforelse
    </tbody></table></div>{{ $reservations->links() }}
</div>
@endsection
