@if($branches->isEmpty())
    <div class="alert alert-warning">Create a branch before adding categories.
        <a href="{{ route('admin.branches.create') }}" class="alert-link">Add branch</a>
    </div>
@endif
<div class="row g-3">
    <div class="col-md-6">
        <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
        <select id="branch_id" name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
            <option value="">Select branch</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $category->branch_id ?? '') === (string) $branch->id)>
                    {{ $branch->organization->name }} — {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="name" class="form-label">Category name <span class="text-danger">*</span></label>
        <input id="name" name="name" value="{{ old('name', $category->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror" required maxlength="255">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="3" maxlength="2000"
                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="image" class="form-label">Category image</label>
        <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
               class="form-control @error('image') is-invalid @enderror">
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if(isset($category) && $category->image)
            <img class="mt-2 rounded" src="{{ asset('storage/'.$category->image) }}"
                 alt="{{ $category->name }}" style="max-width:120px">
        @endif
    </div>
    <div class="col-md-6">
        <label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
        <select id="is_active" name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
            <option value="1" @selected((string) old('is_active', $category->is_active ?? '1') === '1')>Active</option>
            <option value="0" @selected((string) old('is_active', $category->is_active ?? '1') === '0')>Inactive</option>
        </select>
        @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-orange" @disabled($branches->isEmpty())>Save Category</button>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-light border">Cancel</a>
</div>
