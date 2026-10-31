@extends('layouts.admin')
@section('title', 'New Order')
@section('content')
<div class="mb-4"><h3 class="fw-bold mb-1">New Order</h3><p class="text-muted mb-0">Order totals are calculated from current menu prices. Taxes and discounts are not configured.</p></div>
@if($branches->isEmpty())
    <div class="alert alert-warning">Your account is not assigned to a branch that can take orders. Ask an administrator to assign your account.</div>
@endif
<div class="admin-card">
    <form action="{{ route('admin.orders.store') }}" method="POST" id="orderForm">
        @csrf
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                <select id="branch_id" name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                    <option value="">Select branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            data-currency-code="{{ $branch->organization->settings?->currency_code ?? 'INR' }}"
                            data-tax-rate="{{ $branch->organization->settings?->tax_rate ?? 0 }}"
                            data-tax-label="{{ $branch->organization->settings?->tax_label ?? 'Tax' }}"
                            data-service-charge-rate="{{ $branch->organization->settings?->service_charge_rate ?? 0 }}"
                            data-delivery-fee="{{ $branch->organization->settings?->delivery_fee ?? 0 }}"
                            @selected((string) old('branch_id') === (string) $branch->id)>{{ $branch->organization->name }} — {{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="order_type" class="form-label">Order type <span class="text-danger">*</span></label>
                <select id="order_type" name="order_type" class="form-select @error('order_type') is-invalid @enderror" required>
                    @foreach(\App\Models\Order::TYPES as $type)
                        <option value="{{ $type }}" @selected(old('order_type', 'dine_in') === $type)>{{ str_replace('_', ' ', ucfirst($type)) }}</option>
                    @endforeach
                </select>
                @error('order_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6" id="tableField">
                <label for="dining_table_id" class="form-label">Available table <span class="text-danger">*</span></label>
                <select id="dining_table_id" name="dining_table_id" class="form-select @error('dining_table_id') is-invalid @enderror">
                    <option value="">Select table</option>
                    @foreach($tables as $table)
                        <option value="{{ $table->id }}" data-branch-id="{{ $table->branch_id }}" @selected((string) old('dining_table_id') === (string) $table->id)>
                            {{ $table->table_number }} ({{ $table->capacity }} seats)</option>
                    @endforeach
                </select>
                @error('dining_table_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="payment_status" class="form-label">Payment status <span class="text-danger">*</span></label>
                <select id="payment_status" name="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                    <option value="pending" @selected(old('payment_status', 'pending') === 'pending')>Pending</option>
                    <option value="paid" @selected(old('payment_status') === 'paid')>Paid now</option>
                </select>
                @error('payment_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6" id="paymentMethodField">
                <label for="payment_method" class="form-label">Payment method</label>
                <select id="payment_method" name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                    <option value="">Select method</option>
                    @foreach(\App\Models\Order::PAYMENT_METHODS as $method)
                        <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ strtoupper($method) }}</option>
                    @endforeach
                </select>
                @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="customer_name" class="form-label">Customer name</label>
                <input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" maxlength="255" class="form-control @error('customer_name') is-invalid @enderror">
                @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="customer_phone" class="form-label">Customer phone</label>
                <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" maxlength="30" class="form-control @error('customer_phone') is-invalid @enderror">
                @error('customer_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12" id="deliveryAddressField">
                <label for="delivery_address" class="form-label">Delivery address</label>
                <textarea id="delivery_address" name="delivery_address" rows="2" maxlength="2000" class="form-control @error('delivery_address') is-invalid @enderror">{{ old('delivery_address') }}</textarea>
                @error('delivery_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Items <span class="text-danger">*</span></label>
                <div id="orderItems">
                    <div class="row g-2 align-items-end order-item-row mb-2">
                        <div class="col-md-8">
                            <label class="form-label small">Product</label>
                            <select name="items[0][product_id]" class="form-select product-select" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-branch-id="{{ $product->branch_id }}"
                                        data-price="{{ $product->price }}"
                                        @selected((string) old('items.0.product_id') === (string) $product->id)>
                                        {{ $product->name }} — {{ $product->branch->organization->settings?->currency_code ?? 'INR' }} {{ number_format((float) $product->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Quantity</label>
                            <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1" max="100" value="{{ old('items.0.quantity', 1) }}" required>
                        </div>
                        <div class="col-md-1"><button type="button" class="btn btn-light border remove-item" disabled aria-label="Remove item"><i class="bi bi-trash"></i></button></div>
                    </div>
                </div>
                <button type="button" id="addOrderItem" class="btn btn-sm btn-outline-warning mt-2"><i class="bi bi-plus-lg me-1"></i>Add item</button>
                <div class="text-end mt-3">
                    <div>Subtotal: <span id="orderCurrencyCode">INR</span> <span id="subtotalPreview">0.00</span></div>
                    <div id="taxPreviewRow" hidden><span id="taxLabelPreview">Tax</span>: <span class="orderCurrencyCode">INR</span> <span id="taxPreview">0.00</span></div>
                    <div id="servicePreviewRow" hidden>Service charge: <span class="orderCurrencyCode">INR</span> <span id="servicePreview">0.00</span></div>
                    <div id="deliveryPreviewRow" hidden>Delivery fee: <span class="orderCurrencyCode">INR</span> <span id="deliveryPreview">0.00</span></div>
                    <div class="fw-bold fs-5">Total: <span class="orderCurrencyCode">INR</span> <span id="totalPreview">0.00</span></div>
                </div>
                <small class="text-muted d-block text-end">Price is recalculated securely on the server.</small>
            </div>
            <div class="col-12">
                <label for="notes" class="form-label">Order notes</label>
                <textarea id="notes" name="notes" rows="2" maxlength="2000" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-orange" @disabled($branches->isEmpty() || $products->isEmpty())>Create Order</button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchSelect = document.getElementById('branch_id');
    const typeSelect = document.getElementById('order_type');
    const paymentStatus = document.getElementById('payment_status');
    const tableSelect = document.getElementById('dining_table_id');
    const customerName = document.getElementById('customer_name');
    const address = document.getElementById('delivery_address');
    const rows = document.getElementById('orderItems');
    const currencyCode = document.getElementById('orderCurrencyCode');
    let nextIndex = 1;

    const updateForm = () => {
        const branch = branchSelect.selectedOptions[0];
        currencyCode.textContent = branch?.dataset.currencyCode || 'INR';
        document.querySelectorAll('.orderCurrencyCode').forEach((label) => {
            label.textContent = branch?.dataset.currencyCode || 'INR';
        });
        const dineIn = typeSelect.value === 'dine_in';
        const delivery = typeSelect.value === 'delivery';
        document.getElementById('tableField').hidden = !dineIn;
        tableSelect.required = dineIn;
        document.getElementById('deliveryAddressField').hidden = !delivery;
        address.required = delivery;
        customerName.required = delivery;
        document.getElementById('paymentMethodField').hidden = paymentStatus.value !== 'paid';
        document.getElementById('payment_method').required = paymentStatus.value === 'paid';

        document.querySelectorAll('.product-select option[data-branch-id], #dining_table_id option[data-branch-id]').forEach((option) => {
            option.hidden = option.dataset.branchId !== branchSelect.value;
            if (option.hidden && option.selected) option.parentElement.value = '';
        });
        updateTotal();
    };

    const updateTotal = () => {
        let total = 0;
        document.querySelectorAll('.order-item-row').forEach((row) => {
            const option = row.querySelector('.product-select').selectedOptions[0];
            const price = Number(option?.dataset.price || 0);
            const quantity = Number(row.querySelector('.quantity-input').value || 0);
            total += price * quantity;
        });
        const branch = branchSelect.selectedOptions[0];
        const taxRate = Number(branch?.dataset.taxRate || 0);
        const serviceRate = Number(branch?.dataset.serviceChargeRate || 0);
        const deliveryFee = typeSelect.value === 'delivery' ? Number(branch?.dataset.deliveryFee || 0) : 0;
        const tax = Math.round(total * taxRate) / 100;
        const service = Math.round(total * serviceRate) / 100;
        document.getElementById('subtotalPreview').textContent = total.toFixed(2);
        document.getElementById('taxLabelPreview').textContent =
            `${branch?.dataset.taxLabel || 'Tax'} (${taxRate.toFixed(2)}%)`;
        document.getElementById('taxPreview').textContent = tax.toFixed(2);
        document.getElementById('servicePreview').textContent = service.toFixed(2);
        document.getElementById('deliveryPreview').textContent = deliveryFee.toFixed(2);
        document.getElementById('totalPreview').textContent = (total + tax + service + deliveryFee).toFixed(2);
        document.getElementById('taxPreviewRow').hidden = tax <= 0;
        document.getElementById('servicePreviewRow').hidden = service <= 0;
        document.getElementById('deliveryPreviewRow').hidden = deliveryFee <= 0;
    };

    document.getElementById('addOrderItem').addEventListener('click', () => {
        const row = rows.querySelector('.order-item-row').cloneNode(true);
        row.querySelectorAll('select, input').forEach((field) => {
            field.name = field.name.replace(/\[0\]/, `[${nextIndex}]`);
            field.value = field.classList.contains('quantity-input') ? '1' : '';
        });
        row.querySelector('.remove-item').disabled = false;
        rows.appendChild(row);
        nextIndex += 1;
        updateForm();
    });

    rows.addEventListener('click', (event) => {
        if (event.target.closest('.remove-item')) {
            event.target.closest('.order-item-row').remove();
            updateTotal();
        }
    });
    rows.addEventListener('change', updateTotal);
    rows.addEventListener('input', updateTotal);
    branchSelect.addEventListener('change', updateForm);
    typeSelect.addEventListener('change', updateForm);
    paymentStatus.addEventListener('change', updateForm);
    updateForm();
});
</script>
@endpush
