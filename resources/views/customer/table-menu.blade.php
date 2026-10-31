@extends('layouts.guest')
@section('title', 'Order at Table '.$table->table_number)

@push('styles')
<style>
    .qr-page{max-width:980px}.qr-hero{background:linear-gradient(115deg,#172033ed,#17203360),url('{{ asset('images/restaurant-interior.jpg') }}') center/cover;border-radius:20px;color:#fff;padding:1.6rem}
    .qr-category-list{display:flex;gap:.5rem;overflow:auto;padding:.25rem 0 1rem;scrollbar-width:thin}
    .qr-category{border:1px solid #e2e8f0;background:#fff;border-radius:999px;padding:.5rem .9rem;white-space:nowrap;color:#475569}
    .qr-category.active{background:#172033;color:#fff;border-color:#172033}
    .qr-product-card{height:100%;border:1px solid #edf0f4;border-radius:16px;overflow:hidden;background:#fff;transition:transform .18s,box-shadow .18s}
    .qr-product-card:hover{transform:translateY(-2px);box-shadow:0 12px 30px #17203312}
    .qr-product-image{width:100%;height:160px;object-fit:cover;background:#f1f5f9}
    .qr-add-button{min-width:94px}
    .qr-sticky-cart{position:fixed;z-index:1020;bottom:1rem;left:50%;transform:translateX(-50%);width:min(94vw,560px);box-shadow:0 8px 30px #17203340}
    .qr-receipt-totals{width:min(100%,360px)}.qr-receipt-totals>div{display:flex;justify-content:space-between;gap:1rem;padding:.25rem 0}.qr-receipt-grand-total{border-top:1px solid #e2e8f0;margin-top:.35rem;padding-top:.6rem!important;font-size:1.1rem}
    @media(max-width:575px){.qr-page{padding-left:12px;padding-right:12px}.qr-hero{padding:1.15rem;border-radius:16px}.qr-product-image{height:138px}.qr-sticky-cart{bottom:.6rem}}
</style>
@endpush

@section('content')
<main class="container qr-page py-4 pb-5"
      data-order-menu
      data-currency="{{ $table->branch->organization->settings?->currency_code ?? 'INR' }}"
      data-tax-label="{{ $table->branch->organization->settings?->tax_label ?: 'Tax' }}"
      data-tax-rate="{{ $table->branch->organization->settings?->tax_rate ?? 0 }}"
      data-service-rate="{{ $table->branch->organization->settings?->service_charge_rate ?? 0 }}">
    <header class="qr-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                @if($table->branch->organization->logo)
                    <img src="{{ asset('storage/'.$table->branch->organization->logo) }}" alt="" style="width:60px;height:60px;object-fit:contain;background:#fff;border-radius:12px;padding:4px">
                @endif
                <div>
                    <h1 class="h3 fw-bold mb-1">{{ $table->branch->organization->name }}</h1>
                    <p class="mb-2 text-white-50">{{ $table->branch->name }}</p>
                    <span class="badge text-bg-light">Table {{ $table->table_number }}</span>
                </div>
            </div>
            <a href="#qrMenu" class="btn btn-warning fw-semibold">Explore menu <i class="bi bi-arrow-down ms-1"></i></a>
        </div>
        <p class="mt-3 mb-0 text-white-50">Browse the menu, customize your order, and send it directly to the kitchen.</p>
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
                                <tr><td>{{ $item['name'] }}</td><td class="text-center">{{ $item['quantity'] }}</td><td class="text-end">{{ $confirmation['currency_code'] }} {{ number_format((float) $item['unit_price'], 2) }}</td><td class="text-end">{{ $confirmation['currency_code'] }} {{ number_format((float) $item['line_total'], 2) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="qr-receipt-totals ms-auto">
                    <div><span>Subtotal</span><strong>{{ $confirmation['currency_code'] }} {{ number_format((float) $confirmation['subtotal'], 2) }}</strong></div>
                    @if((float) $confirmation['tax_amount'] > 0)<div><span>{{ $confirmation['tax_label'] }}</span><strong>{{ $confirmation['currency_code'] }} {{ number_format((float) $confirmation['tax_amount'], 2) }}</strong></div>@endif
                    @if((float) $confirmation['service_charge'] > 0)<div><span>Service charge</span><strong>{{ $confirmation['currency_code'] }} {{ number_format((float) $confirmation['service_charge'], 2) }}</strong></div>@endif
                    @if((float) $confirmation['delivery_fee'] > 0)<div><span>Delivery fee</span><strong>{{ $confirmation['currency_code'] }} {{ number_format((float) $confirmation['delivery_fee'], 2) }}</strong></div>@endif
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
    @if(session('qr_service_request_status'))
        <div class="alert alert-success" role="status">{{ session('qr_service_request_status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1">Need help at your table?</h2>
            <p class="small text-muted mb-0">Send a request to the restaurant team.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @foreach(['call_waiter' => 'Call waiter', 'request_bill' => 'Request bill', 'request_water' => 'Request water'] as $requestType => $requestLabel)
                <form action="{{ route('qr.service-requests.store', ['token' => $table->qr_token]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="request_type" value="{{ $requestType }}">
                    <button class="btn btn-outline-secondary btn-sm" type="submit"><i class="bi bi-bell me-1"></i>{{ $requestLabel }}</button>
                </form>
            @endforeach
        </div>
    </section>

    <section id="qrMenu">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
            <div><h2 class="h4 fw-bold mb-1">Our menu</h2><p class="text-muted small mb-0">Choose your favorites and review them before placing your order.</p></div>
            @if($products->isNotEmpty())
                <div class="input-group" style="max-width:300px">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="search" class="form-control" id="menuSearch" placeholder="Search menu" aria-label="Search menu">
                </div>
            @endif
        </div>
        @if($products->isEmpty())
            <div class="alert alert-light border text-center">The menu is not available right now. Please ask a staff member for help.</div>
        @else
            <nav class="qr-category-list" aria-label="Menu categories">
                <button class="qr-category active" type="button" data-category-filter="all">All items</button>
                @foreach($products->groupBy('category_id') as $categoryId => $categoryProducts)
                    <button class="qr-category" type="button" data-category-filter="{{ $categoryId }}">{{ $categoryProducts->first()->category?->name ?? 'Other' }}</button>
                @endforeach
            </nav>
            <div class="row g-3 mb-4" id="menuProducts">
                @foreach($products as $product)
                    <div class="col-12 col-sm-6 col-lg-4 menu-product" data-category="{{ $product->category_id }}" data-name="{{ strtolower($product->name.' '.$product->description.' '.$product->category?->name) }}">
                        <article class="qr-product-card">
                            <img class="qr-product-image" src="{{ $product->image ? asset('storage/'.$product->image) : asset('images/menu-food.jpg') }}" alt="{{ $product->name }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/menu-food.jpg') }}'">
                            <div class="p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <h3 class="h6 fw-bold mb-1">{{ $product->name }}</h3>
                                    <span class="fw-semibold text-nowrap">{{ $table->branch->organization->settings?->currency_code ?? 'INR' }} {{ number_format((float) $product->price, 2) }}</span>
                                </div>
                                @if($product->category)<small class="text-muted">{{ $product->category->name }}</small>@endif
                                @if($product->description)<p class="small text-muted mt-2 mb-3">{{ $product->description }}</p>@endif
                                <button type="button" class="btn btn-outline-warning btn-sm qr-add-button add-menu-item"
                                        aria-pressed="false"
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->price }}">
                                    <i class="bi bi-plus-lg me-1"></i>Add
                                </button>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
            <p class="text-muted text-center" id="noMenuResults" hidden>No menu items match your search.</p>

            <div class="card border-0 shadow-sm mb-5" id="orderCart">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                        <div><h2 class="h5 fw-bold mb-1">Your order</h2><span class="small text-muted" id="selectedItemCount" aria-live="polite">0 items selected</span></div>
                        <i class="bi bi-bag-check fs-3 text-warning"></i>
                    </div>
                    <form action="{{ route('qr.orders.store', ['token' => $table->qr_token]) }}" method="POST" id="tableOrderForm">
                        @csrf
                        <div id="cartItems"></div>
                        <div id="emptyCart" class="text-muted text-center py-3">Add items from the menu to get started.</div>
                        <div class="row g-3 mt-2">
                            <div class="col-sm-6"><label for="customer_name" class="form-label">Name <small class="text-muted">(optional)</small></label><input id="customer_name" name="customer_name" maxlength="255" class="form-control" value="{{ old('customer_name') }}"></div>
                            <div class="col-sm-6"><label for="customer_phone" class="form-label">Phone <small class="text-muted">(optional)</small></label><input id="customer_phone" name="customer_phone" type="tel" maxlength="30" class="form-control" value="{{ old('customer_phone') }}"></div>
                            <div class="col-12"><label for="notes" class="form-label">Notes or dietary requests <small class="text-muted">(optional)</small></label><textarea id="notes" name="notes" rows="2" maxlength="1000" class="form-control">{{ old('notes') }}</textarea></div>
                        </div>
                        <div class="border-top mt-3 pt-3">
                            <div class="d-flex justify-content-between"><span>Subtotal</span><span>{{ $table->branch->organization->settings?->currency_code ?? 'INR' }} <span id="cartSubtotal">0.00</span></span></div>
                            <div class="d-flex justify-content-between" id="taxLine" hidden><span id="taxLabel">{{ $table->branch->organization->settings?->tax_label ?: 'Tax' }}</span><span>{{ $table->branch->organization->settings?->currency_code ?? 'INR' }} <span id="cartTax">0.00</span></span></div>
                            <div class="d-flex justify-content-between" id="serviceLine" hidden><span>Service charge</span><span>{{ $table->branch->organization->settings?->currency_code ?? 'INR' }} <span id="cartService">0.00</span></span></div>
                            <div class="d-flex justify-content-between fw-bold fs-5 mt-2"><span>Total</span><span>{{ $table->branch->organization->settings?->currency_code ?? 'INR' }} <span id="cartTotal">0.00</span></span></div>
                            <small class="text-muted d-block mt-2">Pay at the restaurant. Taxes and service charge are included where configured.</small>
                        </div>
                        <button class="btn btn-orange w-100 mt-3" id="submitOrder" type="submit" disabled><i class="bi bi-send me-1"></i>Place order</button>
                    </form>
                </div>
            </div>
        @endif
    </section>
    <footer class="text-center text-muted small mt-4">{{ $table->branch->organization->settings?->receipt_footer }}</footer>
</main>
@if($products->isNotEmpty())
    <button class="btn btn-orange qr-sticky-cart d-flex align-items-center justify-content-between" type="button" id="viewCartButton" hidden>
        <i class="bi bi-bag me-2"></i><span id="stickyCartLabel">View order</span><span class="ms-auto" id="stickyCartTotal"></span>
    </button>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const menu = document.querySelector('[data-order-menu]');
    const cart = new Map();
    const cartItems = document.getElementById('cartItems');
    if (!cartItems) return;
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
        const total = subtotal + tax + service;
        document.getElementById('emptyCart').hidden = cart.size > 0;
        document.getElementById('cartSubtotal').textContent = subtotal.toFixed(2);
        document.getElementById('cartTax').textContent = tax.toFixed(2);
        document.getElementById('cartService').textContent = service.toFixed(2);
        document.getElementById('cartTotal').textContent = total.toFixed(2);
        const selectedQuantity = [...cart.values()].reduce((count, item) => count + item.quantity, 0);
        document.getElementById('selectedItemCount').textContent = `${selectedQuantity} ${selectedQuantity === 1 ? 'item' : 'items'} selected`;
        document.getElementById('taxLine').hidden = tax <= 0;
        document.getElementById('serviceLine').hidden = service <= 0;
        document.getElementById('submitOrder').disabled = cart.size === 0;
        const stickyButton = document.getElementById('viewCartButton');
        if (stickyButton) {
            stickyButton.hidden = cart.size === 0;
            document.getElementById('stickyCartLabel').textContent = `View order · ${selectedQuantity} ${selectedQuantity === 1 ? 'item' : 'items'}`;
            document.getElementById('stickyCartTotal').textContent = `${currency} ${total.toFixed(2)}`;
        }
        addButtons.forEach((button) => {
            const item = cart.get(button.dataset.productId);
            button.setAttribute('aria-pressed', item ? 'true' : 'false');
            button.classList.toggle('btn-warning', Boolean(item));
            button.classList.toggle('btn-outline-warning', !item);
            button.innerHTML = item ? `<i class="bi bi-check2 me-1"></i>Added · ${item.quantity}` : '<i class="bi bi-plus-lg me-1"></i>Add';
        });
    };

    addButtons.forEach((button) => button.addEventListener('click', () => {
        const item = cart.get(button.dataset.productId);
        if (item) item.quantity = Math.min(100, item.quantity + 1);
        else cart.set(button.dataset.productId, {
            name: button.dataset.productName,
            price: Number(button.dataset.productPrice),
            quantity: 1,
        });
        renderCart();
    }));

    previousItems.forEach((item) => {
        const button = availableProducts.get(String(item.product_id));
        if (button) cart.set(String(item.product_id), {
            name: button.dataset.productName,
            price: Number(button.dataset.productPrice),
            quantity: Number(item.quantity),
        });
    });

    let activeCategory = 'all';
    const search = document.getElementById('menuSearch');
    const products = [...document.querySelectorAll('.menu-product')];
    const updateMenuFilter = () => {
        const term = search?.value.trim().toLowerCase() || '';
        let visibleCount = 0;
        products.forEach((product) => {
            const matchesCategory = activeCategory === 'all' || product.dataset.category === activeCategory;
            const matchesTerm = !term || product.dataset.name.includes(term);
            product.hidden = !matchesCategory || !matchesTerm;
            if (!product.hidden) visibleCount += 1;
        });
        document.getElementById('noMenuResults').hidden = visibleCount > 0;
    };
    document.querySelectorAll('[data-category-filter]').forEach((button) => button.addEventListener('click', () => {
        activeCategory = button.dataset.categoryFilter;
        document.querySelectorAll('[data-category-filter]').forEach((categoryButton) => {
            categoryButton.classList.toggle('active', categoryButton === button);
        });
        updateMenuFilter();
    }));
    search?.addEventListener('input', updateMenuFilter);
    document.getElementById('viewCartButton')?.addEventListener('click', () => {
        document.getElementById('orderCart').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    renderCart();
});
</script>
@endpush
