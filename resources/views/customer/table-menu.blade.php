@extends('layouts.guest')
@section('title', 'Order at Table '.$table->table_number)

@section('content')
<div class="container py-4" data-order-menu
     data-currency="{{ $table->branch->organization->settings?->currency_code ?? 'INR' }}"
     data-tax-label="{{ $table->branch->organization->settings?->tax_label ?: 'Tax' }}"
     data-tax-rate="{{ $table->branch->organization->settings?->tax_rate ?? 0 }}"
     data-service-rate="{{ $table->branch->organization->settings?->service_charge_rate ?? 0 }}">
    <header class="text-center mb-4">
        @if($table->branch->organization->logo)
            <img src="{{ asset('storage/'.$table->branch->organization->logo) }}" alt="" style="max-width:96px;max-height:72px;object-fit:contain" class="mb-2">
        @endif
        <h1 class="h3 fw-bold mb-1">{{ $table->branch->organization->name }}</h1>
        <p class="text-muted mb-2">{{ $table->branch->name }}</p>
        <span class="badge text-bg-light border">Table {{ $table->table_number }}</span>
    </header>

    @if(session('qr_order_confirmation.table_id') === $table->id)
        @php($confirmation = session('qr_order_confirmation'))
        <section class="qr-order-receipt card border-success mb-4" aria-labelledby="orderConfirmationTitle">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <span class="badge text-bg-success mb-2"><i class="bi bi-check-circle-fill me-1"></i>Order sent</span>
                        <h2 class="h4 fw-bold mb-1" id="orderConfirmationTitle">Your order is confirmed</h2>
                        <p class="text-muted mb-0">Order {{ $confirmation['order_number'] }} · Table {{ $table->table_number }}</p>
                    </div>
                    <div class="text-md-end">
                        <span class="badge text-bg-warning">Payment due at restaurant</span>
                        <div class="small text-muted mt-2">{{ $confirmation['created_at'] }}</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-3">
                        <thead><tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                            @foreach($confirmation['items'] as $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td class="text-center">{{ $item['quantity'] }}</td>
                                    <td class="text-end">{{ $confirmation['currency_code'] }} {{ number_format((float) $item['unit_price'], 2) }}</td>
                                    <td class="text-end">{{ $confirmation['currency_code'] }} {{ number_format((float) $item['line_total'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="qr-receipt-totals ms-auto">
                    <div><span>Subtotal</span><strong>{{ $confirmation['currency_code'] }} {{ number_format((float) $confirmation['subtotal'], 2) }}</strong></div>
                    @if((float) $confirmation['tax_amount'] > 0)
                        <div><span>{{ $confirmation['tax_label'] }}</span><strong>{{ $confirmation['currency_code'] }} {{ number_format((float) $confirmation['tax_amount'], 2) }}</strong></div>
                    @endif
                    @if((float) $confirmation['service_charge'] > 0)
                        <div><span>Service charge</span><strong>{{ $confirmation['currency_code'] }} {{ number_format((float) $confirmation['service_charge'], 2) }}</strong></div>
                    @endif
                    @if((float) $confirmation['delivery_fee'] > 0)
                        <div><span>Delivery fee</span><strong>{{ $confirmation['currency_code'] }} {{ number_format((float) $confirmation['delivery_fee'], 2) }}</strong></div>
                    @endif
                    <div class="qr-receipt-grand-total"><span>Total due</span><strong>{{ $confirmation['currency_code'] }} {{ number_format((float) $confirmation['total'], 2) }}</strong></div>
                </div>
                @if($confirmation['customer_name'] || $confirmation['notes'])
                    <div class="small text-muted border-top mt-3 pt-3">
                        @if($confirmation['customer_name'])<div>Name: {{ $confirmation['customer_name'] }}</div>@endif
                        @if($confirmation['notes'])<div>Notes: {{ $confirmation['notes'] }}</div>@endif
                    </div>
                @endif
                <p class="small text-muted mt-3 mb-0">The restaurant has received your order. Please pay at the restaurant.</p>
            </div>
        </section>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @if($table->status === 'occupied')
        <div class="alert alert-info text-center">You can add another order for this table.</div>
    @endif

    <h2 class="h5 fw-bold mb-3">Menu</h2>
    @if($products->isEmpty())
        <div class="alert alert-light border text-center">The menu is not available right now. Please ask a staff member for help.</div>
    @else
        <div class="row g-3 mb-4">
            @foreach($products as $product)
                <div class="col-12 col-sm-6">
                    <article class="card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex gap-3 align-items-start">
                            @if($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt=""
                                     style="width:76px;height:76px;object-fit:cover;border-radius:10px">
                            @endif
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between gap-2">
                                    <h3 class="h6 fw-bold mb-1">{{ $product->name }}</h3>
                                    <span class="fw-semibold text-nowrap">{{ $table->branch->organization->settings?->currency_code ?? 'INR' }} {{ number_format((float) $product->price, 2) }}</span>
                                </div>
                                @if($product->category)<small class="text-muted">{{ $product->category->name }}</small>@endif
                                @if($product->description)<p class="small text-muted mt-2 mb-2">{{ $product->description }}</p>@endif
                                <button type="button" class="btn btn-sm btn-outline-warning add-menu-item"
                                        aria-pressed="false"
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->price }}">
                                    <i class="bi bi-plus-lg me-1"></i>Add
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <h2 class="h5 fw-bold mb-0">Your order</h2>
                    <span class="badge text-bg-light border" id="selectedItemCount" aria-live="polite">0 items selected</span>
                </div>
                <form action="{{ route('qr.orders.store', ['token' => $table->qr_token]) }}" method="POST" id="tableOrderForm">
                    @csrf
                    <div id="cartItems"></div>
                    <div id="emptyCart" class="text-muted text-center py-3">Add items from the menu to get started.</div>
                    <div class="row g-3 mt-2">
                        <div class="col-sm-6">
                            <label for="customer_name" class="form-label">Name <small class="text-muted">(optional)</small></label>
                            <input id="customer_name" name="customer_name" maxlength="255" class="form-control" value="{{ old('customer_name') }}">
                        </div>
                        <div class="col-sm-6">
                            <label for="customer_phone" class="form-label">Phone <small class="text-muted">(optional)</small></label>
                            <input id="customer_phone" name="customer_phone" type="tel" maxlength="30" class="form-control" value="{{ old('customer_phone') }}">
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes or dietary requests <small class="text-muted">(optional)</small></label>
                            <textarea id="notes" name="notes" rows="2" maxlength="1000" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="border-top mt-3 pt-3">
                        <div class="d-flex justify-content-between"><span>Subtotal</span><span>{{ $table->branch->organization->settings?->currency_code ?? 'INR' }} <span id="cartSubtotal">0.00</span></span></div>
                        <div class="d-flex justify-content-between" id="taxLine" hidden><span id="taxLabel">{{ $table->branch->organization->settings?->tax_label ?: 'Tax' }}</span><span>{{ $table->branch->organization->settings?->currency_code ?? 'INR' }} <span id="cartTax">0.00</span></span></div>
                        <div class="d-flex justify-content-between" id="serviceLine" hidden><span>Service charge</span><span>{{ $table->branch->organization->settings?->currency_code ?? 'INR' }} <span id="cartService">0.00</span></span></div>
                        <div class="d-flex justify-content-between fw-bold fs-5 mt-2"><span>Total</span><span>{{ $table->branch->organization->settings?->currency_code ?? 'INR' }} <span id="cartTotal">0.00</span></span></div>
                        <small class="text-muted d-block mt-2">Pay at the restaurant. Taxes and service charge are included where configured.</small>
                    </div>
                    <button class="btn btn-orange w-100 mt-3" id="submitOrder" type="submit" disabled>
                        <i class="bi bi-send me-1"></i>Place order
                    </button>
                </form>
            </div>
        </div>
    @endif
    <footer class="text-center text-muted small mt-4">
        {{ $table->branch->organization->settings?->receipt_footer }}
    </footer>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const menu = document.querySelector('[data-order-menu]');
    const cart = new Map();
    const cartItems = document.getElementById('cartItems');
    const addButtons = [...document.querySelectorAll('.add-menu-item')];
    const availableProducts = new Map(addButtons.map((button) => [button.dataset.productId, button]));
    const previousItems = @json(old('items', []));
    const currency = menu.dataset.currency || 'INR';
    const taxRate = Number(menu.dataset.taxRate || 0);
    const serviceRate = Number(menu.dataset.serviceRate || 0);
    const renderCart = () => {
        cartItems.replaceChildren();
        let subtotal = 0;
        let index = 0;
        cart.forEach((item, productId) => {
            subtotal += item.price * item.quantity;
            const row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-2 border-bottom py-2';
            const name = document.createElement('div');
            name.className = 'flex-grow-1';
            name.textContent = item.name;
            const quantity = document.createElement('input');
            quantity.type = 'number';
            quantity.min = '1';
            quantity.max = '100';
            quantity.value = String(item.quantity);
            quantity.className = 'form-control form-control-sm';
            quantity.style.width = '76px';
            quantity.setAttribute('aria-label', `Quantity for ${item.name}`);
            quantity.addEventListener('change', () => {
                const nextQuantity = Number(quantity.value);
                if (!Number.isInteger(nextQuantity) || nextQuantity < 1 || nextQuantity > 100) {
                    quantity.value = String(item.quantity);
                    return;
                }
                item.quantity = nextQuantity;
                renderCart();
            });
            const lineTotal = document.createElement('span');
            lineTotal.className = 'text-nowrap';
            lineTotal.textContent = `${currency} ${(item.price * item.quantity).toFixed(2)}`;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-light text-danger';
            remove.setAttribute('aria-label', `Remove ${item.name}`);
            remove.innerHTML = '<i class="bi bi-trash"></i>';
            remove.addEventListener('click', () => {
                cart.delete(productId);
                renderCart();
            });
            const productInput = document.createElement('input');
            productInput.type = 'hidden';
            productInput.name = `items[${index}][product_id]`;
            productInput.value = productId;
            const quantityInput = document.createElement('input');
            quantityInput.type = 'hidden';
            quantityInput.name = `items[${index}][quantity]`;
            quantityInput.value = String(item.quantity);
            row.append(name, quantity, lineTotal, remove, productInput, quantityInput);
            cartItems.append(row);
            index += 1;
        });

        const tax = Math.round(subtotal * taxRate) / 100;
        const service = Math.round(subtotal * serviceRate) / 100;
        document.getElementById('emptyCart').hidden = cart.size > 0;
        document.getElementById('cartSubtotal').textContent = subtotal.toFixed(2);
        document.getElementById('cartTax').textContent = tax.toFixed(2);
        document.getElementById('cartService').textContent = service.toFixed(2);
        document.getElementById('cartTotal').textContent = (subtotal + tax + service).toFixed(2);
        const selectedQuantity = [...cart.values()].reduce((total, item) => total + item.quantity, 0);
        document.getElementById('selectedItemCount').textContent = `${selectedQuantity} ${selectedQuantity === 1 ? 'item' : 'items'} selected`;
        document.getElementById('taxLine').hidden = tax <= 0;
        document.getElementById('serviceLine').hidden = service <= 0;
        document.getElementById('submitOrder').disabled = cart.size === 0;
        addButtons.forEach((button) => {
            const item = cart.get(button.dataset.productId);
            button.setAttribute('aria-pressed', item ? 'true' : 'false');
            button.classList.toggle('btn-warning', Boolean(item));
            button.classList.toggle('btn-outline-warning', !item);
            button.innerHTML = item
                ? `<i class="bi bi-check-lg me-1"></i>Added · ${item.quantity}`
                : '<i class="bi bi-plus-lg me-1"></i>Add';
        });
    };

    addButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.productId;
            const current = cart.get(id);
            if (current && current.quantity >= 100) return;
            if (current) current.quantity += 1;
            else cart.set(id, {
                name: button.dataset.productName,
                price: Number(button.dataset.productPrice),
                quantity: 1,
            });
            renderCart();
        });
    });

    previousItems.forEach((item) => {
        const button = availableProducts.get(String(item.product_id));
        const quantity = Number(item.quantity);
        if (!button || !Number.isInteger(quantity) || quantity < 1 || quantity > 100) return;

        cart.set(button.dataset.productId, {
            name: button.dataset.productName,
            price: Number(button.dataset.productPrice),
            quantity,
        });
    });
    renderCart();

    document.getElementById('tableOrderForm')?.addEventListener('submit', (event) => {
        if (cart.size === 0) {
            event.preventDefault();
            return;
        }
        document.getElementById('submitOrder').disabled = true;
        document.getElementById('submitOrder').textContent = 'Sending order...';
    });
});
</script>
@endpush
