@extends('layouts.admin')
@section('title', 'New Reservation')
@section('content')
<div class="mb-4"><h3 class="fw-bold">New reservation</h3><p class="text-muted mb-0">Choose a time window and optional table. Overlapping table bookings are blocked.</p></div>
<div class="admin-card"><form method="POST" action="{{ route('admin.reservations.store') }}" data-ajax-form>@csrf
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Branch</label><select class="form-select" name="branch_id" data-reservation-branch required>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label">Table (optional)</label><select class="form-select" name="dining_table_id" data-reservation-table><option value="">Assign later</option>@foreach($branches as $branch)@foreach($branch->diningTables as $table)<option value="{{ $table->id }}" data-branch="{{ $branch->id }}" @selected(old('dining_table_id') == $table->id)>{{ $branch->name }} — {{ $table->table_number }} ({{ $table->capacity }} seats)</option>@endforeach @endforeach</select></div>
        <div class="col-md-6"><label class="form-label">Guest name</label><input class="form-control" name="customer_name" value="{{ old('customer_name') }}" required></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="customer_phone" value="{{ old('customer_phone') }}" required maxlength="30"></div>
        <div class="col-md-4"><label class="form-label">Party size</label><input class="form-control" type="number" min="1" max="100" name="party_size" value="{{ old('party_size', 2) }}" required></div>
        <div class="col-md-4"><label class="form-label">Starts at</label><input class="form-control" type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required></div>
        <div class="col-md-4"><label class="form-label">Ends at</label><input class="form-control" type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" required></div>
        <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea></div>
    </div><div class="mt-4"><button class="btn btn-orange">Create reservation</button> <a class="btn btn-outline-secondary" href="{{ route('admin.reservations.index') }}">Cancel</a></div>
</form></div>
@endsection
