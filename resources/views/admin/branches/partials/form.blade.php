@if($organizations->isEmpty())
    <div class="alert alert-warning">
        Create an organization before adding a branch.
        <a href="{{ route('admin.organizations.create') }}" class="alert-link">Add organization</a>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label for="organization_id" class="form-label">Organization <span class="text-danger">*</span></label>
        <select id="organization_id" name="organization_id"
                class="form-select @error('organization_id') is-invalid @enderror" required>
            <option value="">Select organization</option>
            @foreach($organizations as $organization)
                <option value="{{ $organization->id }}"
                    @selected((string) old('organization_id', $branch->organization_id ?? '') === (string) $organization->id)>
                    {{ $organization->name }}
                </option>
            @endforeach
        </select>
        @error('organization_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="name" class="form-label">Branch name <span class="text-danger">*</span></label>
        <input id="name" name="name" value="{{ old('name', $branch->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror" required maxlength="255">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="branch_code" class="form-label">Branch code <span class="text-danger">*</span></label>
        <input id="branch_code" name="branch_code"
               value="{{ old('branch_code', $branch->branch_code ?? '') }}"
               class="form-control @error('branch_code') is-invalid @enderror" required maxlength="30">
        @error('branch_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $branch->email ?? '') }}"
               class="form-control @error('email') is-invalid @enderror" maxlength="255">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="phone" class="form-label">Phone</label>
        <input id="phone" name="phone" value="{{ old('phone', $branch->phone ?? '') }}"
               class="form-control @error('phone') is-invalid @enderror" maxlength="20">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="pincode" class="form-label">Pincode</label>
        <input id="pincode" name="pincode" value="{{ old('pincode', $branch->pincode ?? '') }}"
               class="form-control @error('pincode') is-invalid @enderror" maxlength="10">
        @error('pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="city" class="form-label">City</label>
        <input id="city" name="city" value="{{ old('city', $branch->city ?? '') }}"
               class="form-control @error('city') is-invalid @enderror" maxlength="255">
        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="state" class="form-label">State</label>
        <input id="state" name="state" value="{{ old('state', $branch->state ?? '') }}"
               class="form-control @error('state') is-invalid @enderror" maxlength="255">
        @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="address" class="form-label">Address</label>
        <textarea id="address" name="address" rows="3"
                  class="form-control @error('address') is-invalid @enderror">{{ old('address', $branch->address ?? '') }}</textarea>
        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" @selected(old('status', $branch->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $branch->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-orange" @disabled($organizations->isEmpty())>Save Branch</button>
    <a href="{{ route('admin.branches.index') }}" class="btn btn-light border">Cancel</a>
</div>
