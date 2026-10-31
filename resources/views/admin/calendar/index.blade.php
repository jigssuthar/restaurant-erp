@extends('layouts.admin')
@section('title', 'Calendar & Events')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">Calendar & Events</h3><p class="text-muted mb-0">Plan restaurant events, holidays, promotions, and maintenance.</p></div>
    <a class="btn btn-orange" href="{{ route('admin.calendar.create') }}"><i class="bi bi-plus-lg me-2"></i>Add event</a>
</div>
@include('admin.partials.feedback')
<div class="admin-table-card">
    <form method="GET" action="{{ route('admin.calendar.index') }}" class="row g-2 mb-4" data-ajax-filter>
        <div class="col-md-4"><label class="form-label">Month</label><input class="form-control" type="month" name="month" value="{{ $month->format('Y-m') }}"></div>
        <div class="col-md-5"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-outline-secondary w-100">Apply filters</button></div>
    </form>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>EVENT</th><th>BRANCH</th><th>TYPE</th><th>DATE / TIME</th><th>STATUS</th><th></th></tr></thead><tbody>
    @forelse($events as $event)
        <tr><td class="fw-semibold">{{ $event->title }}<br><small class="text-muted">{{ $event->description }}</small></td><td>{{ $event->branch->name }}</td><td>{{ str_replace('_', ' ', ucfirst($event->event_type)) }}</td><td>{{ $event->starts_at->format('d M Y, H:i') }}<br><small>to {{ $event->ends_at->format('d M Y, H:i') }}</small></td><td><span class="badge bg-light text-dark">{{ ucfirst($event->status) }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.calendar.edit', $event) }}">Edit</a><form class="d-inline" method="POST" action="{{ route('admin.calendar.destroy', $event) }}" data-ajax-form>@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" data-confirm="Delete this event?">Delete</button></form></td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted py-5">No events for this period.</td></tr>@endforelse
    </tbody></table></div>{{ $events->links() }}
</div>
@endsection
