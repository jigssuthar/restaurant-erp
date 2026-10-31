@extends('layouts.admin')
@section('title', $event->exists ? 'Edit Event' : 'Add Event')
@section('content')
<div class="mb-4"><h3 class="fw-bold">{{ $event->exists ? 'Edit event' : 'Add calendar event' }}</h3><p class="text-muted mb-0">Set branch, schedule, and event details.</p></div>
<div class="admin-card">
    <form method="POST" action="{{ $event->exists ? route('admin.calendar.update', $event) : route('admin.calendar.store') }}" data-ajax-form>
        @csrf @if($event->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Branch</label><select class="form-select" name="branch_id" required>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(old('branch_id', $event->branch_id) == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">Title</label><input class="form-control" name="title" value="{{ old('title', $event->title) }}" required maxlength="180"></div>
            <div class="col-md-4"><label class="form-label">Type</label><select class="form-select" name="event_type">@foreach(\App\Models\RestaurantEvent::TYPES as $type)<option value="{{ $type }}" @selected(old('event_type', $event->event_type ?: 'general') === $type)>{{ str_replace('_', ' ', ucfirst($type)) }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Starts at</label><input class="form-control" type="datetime-local" name="starts_at" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}" required></div>
            <div class="col-md-4"><label class="form-label">Ends at</label><input class="form-control" type="datetime-local" name="ends_at" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}" required></div>
            <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status">@foreach(['scheduled','completed','cancelled'] as $status)<option value="{{ $status }}" @selected(old('status', $event->status ?: 'scheduled') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="col-md-8"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2">{{ old('description', $event->description) }}</textarea></div>
            <div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" name="all_day" value="1" @checked(old('all_day', $event->all_day))><span class="form-check-label">All-day event</span></label></div>
        </div>
        <div class="mt-4"><button class="btn btn-orange">{{ $event->exists ? 'Save changes' : 'Create event' }}</button> <a class="btn btn-outline-secondary" href="{{ route('admin.calendar.index') }}">Cancel</a></div>
    </form>
</div>
@endsection
