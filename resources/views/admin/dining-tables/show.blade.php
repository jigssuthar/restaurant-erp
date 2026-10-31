@extends('layouts.admin')
@section('title', 'Table '.$table->table_number)
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div><h3 class="fw-bold mb-1">Table {{ $table->table_number }}</h3>
        <p class="text-muted mb-0">{{ $table->branch->organization->name }} — {{ $table->branch->name }}</p></div>
    <div class="d-flex gap-2"><a href="{{ route('admin.dining-tables.edit', $table) }}" class="btn btn-orange">Edit Table</a>
        <a href="{{ route('admin.dining-tables.index') }}" class="btn btn-light border">Back</a></div>
</div>
<div class="admin-card"><div class="row g-4">
    <div class="col-md-4"><strong>Branch</strong><div>{{ $table->branch->name }}</div></div>
    <div class="col-md-4"><strong>Capacity</strong><div>{{ $table->capacity }} guests</div></div>
    <div class="col-md-4"><strong>Status</strong><div>{{ ucfirst($table->status) }}</div></div>
</div></div>
<div class="admin-card">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h5 class="fw-bold mb-1">Table ordering QR code</h5>
            <p class="text-muted mb-2">Customers can scan this code to browse the menu and order directly to this table.</p>
            <a href="{{ route('qr.menu', ['token' => $table->qr_token]) }}" target="_blank" rel="noopener">
                {{ route('qr.menu', ['token' => $table->qr_token]) }}
            </a>
        </div>
        <form action="{{ route('admin.dining-tables.qr.rotate', $table) }}" method="POST"
              onsubmit="return confirm('Replace this QR code? All previously printed codes will stop working.')">
            @csrf
            <button type="submit" class="btn btn-outline-danger">
                <i class="bi bi-arrow-clockwise me-1"></i>Replace QR code
            </button>
        </form>
    </div>
    <div class="mt-4 d-flex flex-column align-items-start">
        <canvas id="tableQrCode" data-qr-code="{{ route('qr.menu', ['token' => $table->qr_token]) }}"
                aria-label="QR code for table {{ $table->table_number }}"></canvas>
        <div class="d-flex gap-2 mt-3">
            <button type="button" class="btn btn-orange" data-qr-download data-canvas="#tableQrCode"
                    data-filename="table-{{ \Illuminate\Support\Str::slug($table->table_number) }}-qr.png">
                <i class="bi bi-download me-1"></i>Download QR
            </button>
            <button type="button" class="btn btn-light border" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>
</div>
@push('styles')
<style>
@media print {
    .admin-sidebar, .admin-header, .sidebar-overlay, .admin-card form, .admin-card .btn { display: none !important; }
    .admin-main { margin: 0 !important; width: 100% !important; }
    .admin-content { padding: 0 !important; }
}
</style>
@endpush
@endsection
