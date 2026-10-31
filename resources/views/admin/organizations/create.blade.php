
@extends('layouts.admin')

@section('title', 'Add Organization')

@section('content')

<div class="mb-4">
    <a href="{{ route('admin.organizations.index') }}"
       class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left"></i> Back to Organizations
    </a>

    <h3 class="fw-bold mt-3 mb-1">Add Organization</h3>
    <p class="text-muted">Create a new restaurant business profile.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.organizations.store') }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="admin-card mb-4">

                <h5 class="fw-bold mb-4">
                    <i class="bi bi-building text-warning me-2"></i>
                    Basic Information
                </h5>

                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label">Organization Name *</label>
                        <input type="text" name="name"
                               class="form-control"
                               value="{{ old('name') }}"
                               placeholder="Enter organization name"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email"
                               class="form-control"
                               value="{{ old('email') }}"
                               placeholder="business@example.com">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone"
                               class="form-control"
                               value="{{ old('phone') }}"
                               placeholder="+91 XXXXX XXXXX">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Business Address</label>
                        <textarea name="address"
                                  rows="4"
                                  class="form-control"
                                  placeholder="Enter complete business address">{{ old('address') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">GST Number</label>
                        <input type="text" name="gst_number"
                               class="form-control"
                               value="{{ old('gst_number') }}"
                               placeholder="Enter GST number">
                    </div>

                </div>

            </div>

            <div class="admin-card">

                <h5 class="fw-bold mb-4">
                    <i class="bi bi-image text-warning me-2"></i>
                    Organization Logo
                </h5>

                <input type="file"
                       name="logo"
                       id="organizationLogo"
                       class="form-control"
                       accept="image/*">

                <div id="logoPreview" class="mt-3"></div>

                <small class="text-muted">
                    JPG, PNG or WEBP. Maximum file size 2MB.
                </small>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="admin-card">

                <h5 class="fw-bold mb-4">Organization Settings</h5>

                <label class="form-label">Status *</label>

                <select name="status" class="form-select mb-4">
                    <option value="active" @selected(old('status', 'active') == 'active')>Active</option>
                    <option value="inactive" @selected(old('status') == 'inactive')>Inactive</option>
                </select>

                <hr>

                <button type="submit" class="btn btn-orange w-100 mb-2">
                    <i class="bi bi-check-circle me-2"></i>
                    Save Organization
                </button>

                <a href="{{ route('admin.organizations.index') }}"
                   class="btn btn-light border w-100">
                    Cancel
                </a>

            </div>

        </div>

    </div>

</form>

@endsection

@push('scripts')
<script>
document.getElementById('organizationLogo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('logoPreview');

    preview.innerHTML = '';

    if (!file) return;

    const image = document.createElement('img');
    image.src = URL.createObjectURL(file);
    image.style.width = '100px';
    image.style.height = '100px';
    image.style.objectFit = 'cover';
    image.style.borderRadius = '12px';

    preview.appendChild(image);
});
</script>
@endpush
