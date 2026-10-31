@if($branches->isEmpty())
    <div class="alert alert-warning">Create a branch before adding dining tables.
        <a href="{{ route('admin.branches.create') }}" class="alert-link">Add branch</a></div>
@endif
<div class="row g-3">
    <div class="col-md-6">
        <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
        <select id="branch_id" name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
            <option value="">Select branch</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $table->branch_id ?? '') === (string) $branch->id)>{{ $branch->organization->name }} — {{ $branch->name }}</option>
            @endforeach
        </select>
        @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="table_number" class="form-label">Table number <span class="text-danger">*</span></label>
        <input id="table_number" name="table_number" value="{{ old('table_number', $table->table_number ?? '') }}" maxlength="255" required
               class="form-control @error('table_number') is-invalid @enderror">
        @error('table_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="capacity" class="form-label">Seating capacity <span class="text-danger">*</span></label>
        <input id="capacity" type="number" name="capacity" value="{{ old('capacity', $table->capacity ?? 4) }}" min="1" max="1000" required
               class="form-control @error('capacity') is-invalid @enderror">
        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach(['available', 'occupied', 'reserved', 'maintenance'] as $status)
                <option value="{{ $status }}" @selected(old('status', $table->status ?? 'available') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-orange" @disabled($branches->isEmpty())>Save Table</button>
    <a href="{{ route('admin.dining-tables.index') }}" class="btn btn-light border">Cancel</a>
</div>
