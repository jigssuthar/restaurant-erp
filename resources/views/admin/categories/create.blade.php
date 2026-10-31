@extends('layouts.admin')

@section('title', 'Add Category')

@section('content')
<div class="mb-4"><h3 class="fw-bold mb-1">Add Category</h3><p class="text-muted mb-0">Create a menu category.</p></div>
<div class="admin-card">
    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" data-ajax-form>
        @csrf
        @include('admin.categories.partials.form')
    </form>
</div>
@endsection
