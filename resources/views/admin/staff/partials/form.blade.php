<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
        <input id="name" name="name" value="{{ old('name', $staff->name ?? '') }}" maxlength="255" required
               class="form-control @error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input id="email" type="email" name="email" value="{{ old('email', $staff->email ?? '') }}" maxlength="255" required
               class="form-control @error('email') is-invalid @enderror">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
            @foreach($roles as $role)
                <option value="{{ $role }}" @selected(old('role', $staff->role ?? 'staff') === $role)>{{ ucfirst($role) }}</option>
            @endforeach
        </select>
        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @if(auth()->user()->isPlatformAdmin())
    <div class="col-md-6">
        <label for="organization_id" class="form-label">Organization</label>
        <select id="organization_id" name="organization_id" class="form-select @error('organization_id') is-invalid @enderror">
            <option value="">No organization assignment</option>
            @foreach($organizations as $organization)
                <option value="{{ $organization->id }}" @selected((string) old('organization_id', $staff->organization_id ?? '') === (string) $organization->id)>{{ $organization->name }}</option>
            @endforeach
        </select>
        @error('organization_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @endif
    <div class="col-md-6">
        <label for="branch_id" class="form-label">Branch</label>
        <select id="branch_id" name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
            <option value="">No branch assignment</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" data-organization-id="{{ $branch->organization_id }}"
                    @selected((string) old('branch_id', $staff->branch_id ?? '') === (string) $branch->id)>
                    {{ $branch->organization->name }} — {{ $branch->name }}</option>
            @endforeach
        </select>
        @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="password" class="form-label">Password @if(!isset($staff))<span class="text-danger">*</span>@endif</label>
        <input id="password" type="password" name="password" autocomplete="new-password" minlength="12"
               class="form-control @error('password') is-invalid @enderror" @required(!isset($staff))>
        @if(isset($staff))<div class="form-text">Leave blank to keep the current password.</div>@endif
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="form-control">
    </div>
</div>
<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-orange">Save Account</button>
    <a href="{{ route('admin.staff.index') }}" class="btn btn-light border">Cancel</a>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const organizationSelect = document.getElementById('organization_id');
    const branchSelect = document.getElementById('branch_id');
    const roleSelect = document.getElementById('role');
    const updateAssignments = () => {
        if (organizationSelect) {
            organizationSelect.required = ['manager', 'staff', 'waiter', 'captain', 'chef', 'kitchen_manager'].includes(roleSelect.value);
        }
        branchSelect.required = ['staff', 'waiter', 'captain', 'chef', 'kitchen_manager'].includes(roleSelect.value);
    };
    const updateBranches = () => {
        if (!organizationSelect) return;
        Array.from(branchSelect.options).forEach((option) => {
            if (option.value) {
                option.hidden = option.dataset.organizationId !== organizationSelect.value;
                if (option.hidden && option.selected) branchSelect.value = '';
            }
        });
    };
    organizationSelect.addEventListener('change', updateBranches);
    roleSelect.addEventListener('change', updateAssignments);
    updateBranches();
    updateAssignments();
});
</script>
@endpush
