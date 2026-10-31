
@extends('layouts.admin')

@section('title', 'Edit Organization')

@section('content')

<div class="mb-4">
    <a href="{{ route('admin.organizations.index') }}"
       class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left"></i> Back to Organizations
    </a>

    <h3 class="fw-bold mt-3">Edit Organization</h3>
    <p class="text-muted">Update {{ $organization->name }} details.</p>
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

<form action="{{ route('admin.organizations.update', $organization) }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf
    @method('PUT')

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="admin-card mb-4">

                <h5 class="fw-bold mb-4">Business Information</h5>

                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label">Organization Name *</label>
                        <input type="text" name="name"
                               class="form-control"
                               value="{{ old('name', $organization->name) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email"
                               class="form-control"
                               value="{{ old('email', $organization->email) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone"
                               class="form-control"
                               value="{{ old('phone', $organization->phone) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address"
                                  class="form-control"
                                  rows="4">{{ old('address', $organization->address) }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">GST Number</label>
                        <input type="text" name="gst_number"
                               class="form-control"
                               value="{{ old('gst_number', $organization->gst_number) }}">
                    </div>

                </div>

            </div>

            <div class="admin-card">

                <h5 class="fw-bold mb-3">Organization Logo</h5>

                @if($organization->logo)
                    <img src="{{ asset('storage/'.$organization->logo) }}"
                         width="90" height="90"
                         class="rounded mb-3"
                         style="object-fit:cover"
                         alt="Current logo">
                @endif

                <input type="file" name="logo"
                       class="form-control"
                       accept="image/*">

            </div>

        </div>

        <div class="col-lg-4">

            <div class="admin-card">

                <h5 class="fw-bold mb-4">Settings</h5>

                <label class="form-label">Status</label>

                <select name="status" class="form-select mb-4">
                    <option value="active" @selected(old('status', $organization->status) == 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $organization->status) == 'inactive')>Inactive</option>
                </select>

                <button class="btn btn-orange w-100 mb-2">
                    <i class="bi bi-save me-2"></i>Update Organization
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
