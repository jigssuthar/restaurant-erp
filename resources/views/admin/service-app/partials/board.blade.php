<div class="service-board-content">
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-4"><div class="admin-card p-3 mb-0 d-flex justify-content-between"><span>Tables</span><strong>{{ $tables->count() }}</strong></div></div>
        <div class="col-6 col-lg-4"><div class="admin-card p-3 mb-0 d-flex justify-content-between"><span>Requests needing attention</span><strong>{{ $pendingRequestCount }}</strong></div></div>
        <div class="col-12 col-lg-4"><div class="admin-card p-3 mb-0 d-flex justify-content-between"><span>Orders ready to serve</span><strong>{{ $readyOrderCount }}</strong></div></div>
    </div>
    @if($tables->isEmpty())
        <div class="alert alert-info">No tables are set up for this branch yet.</div>
    @else
        <div class="row g-3">
            @foreach($tables as $table)
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="service-table-card" data-state="{{ $table->status }}">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div><h2 class="h5 fw-bold mb-1">Table {{ $table->table_number }}</h2><span class="badge {{ $table->status === 'occupied' ? 'text-bg-warning' : 'text-bg-success' }}">{{ ucfirst($table->status) }}</span></div>
                            <span class="small text-muted">Capacity {{ $table->capacity }}</span>
                        </div>
                        @forelse($table->orders as $order)
                            @php
                                $received = (float) $order->payments->where('transaction_type', 'payment')->sum('amount')
                                    - (float) $order->payments->where('transaction_type', 'refund')->sum('amount');
                                $remaining = max(0, round((float) $order->total - $received, 2));
                            @endphp
                            <div class="service-order">
                                <div class="d-flex justify-content-between gap-2"><strong>{{ $order->order_number }}</strong><span class="badge text-bg-light">{{ ucfirst($order->status) }}</span></div>
                                <div class="small text-muted mt-1">{{ $order->items->sum('quantity') }} items · {{ $order->currency_code }} {{ number_format((float) $order->total, 2) }}</div>
                                <ul class="small my-2 ps-3">
                                    @foreach($order->items as $item)
                                        <li>{{ $item->product_name }} × {{ $item->quantity }}</li>
                                    @endforeach
                                </ul>
                                <div class="small mb-2">Payment: <strong>{{ ucfirst($order->payment_status) }}</strong>
                                    @if($remaining > 0) · Due {{ $order->currency_code }} {{ number_format($remaining, 2) }} @endif
                                </div>
                                @if($order->status === 'ready')
                                    <form action="{{ route('service-app.orders.status', $order) }}" method="POST" data-ajax-form class="mb-2">
                                        @csrf @method('PATCH')<input type="hidden" name="status" value="completed">
                                        <button class="btn btn-success btn-sm w-100" type="submit"><i class="bi bi-check-circle me-1"></i>Mark served</button>
                                    </form>
                                @endif
                                @if($remaining > 0)
                                    <details>
                                        <summary class="btn btn-outline-primary btn-sm w-100">Collect payment</summary>
                                        <form action="{{ route('service-app.orders.payments.store', $order) }}" method="POST" data-ajax-form class="border rounded p-2 mt-2">
                                            @csrf
                                            <input type="hidden" name="payments[0][amount]" value="{{ number_format($remaining, 2, '.', '') }}">
                                            <label class="form-label small" for="payment-{{ $order->id }}">Amount due: {{ $order->currency_code }} {{ number_format($remaining, 2) }}</label>
                                            <select id="payment-{{ $order->id }}" name="payments[0][payment_method]" class="form-select form-select-sm mb-2" required>
                                                <option value="cash">Cash</option><option value="upi">UPI</option><option value="card">Card</option><option value="other">Other</option>
                                            </select>
                                            <button class="btn btn-primary btn-sm w-100" type="submit">Record payment</button>
                                        </form>
                                    </details>
                                @endif
                            </div>
                        @empty
                            <p class="small text-muted mt-3 mb-0">No active orders.</p>
                        @endforelse
                        @foreach($table->serviceRequests as $serviceRequest)
                            <div class="service-request">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong>{{ ucwords(str_replace('_', ' ', $serviceRequest->request_type)) }}</strong>
                                    <span class="badge {{ $serviceRequest->status === 'pending' ? 'text-bg-danger' : 'text-bg-info' }}">{{ ucfirst($serviceRequest->status) }}</span>
                                </div>
                                @if($serviceRequest->note)<p class="small text-muted mt-1 mb-2">{{ $serviceRequest->note }}</p>@endif
                                <div class="d-flex gap-2 mt-2">
                                    @if($serviceRequest->status === 'pending')
                                        <form action="{{ route('service-app.requests.status', $serviceRequest) }}" method="POST" data-ajax-form class="flex-fill">
                                            @csrf @method('PATCH')<input type="hidden" name="status" value="acknowledged">
                                            <button class="btn btn-outline-primary btn-sm w-100" type="submit">Acknowledge</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('service-app.requests.status', $serviceRequest) }}" method="POST" data-ajax-form class="flex-fill">
                                        @csrf @method('PATCH')<input type="hidden" name="status" value="completed">
                                        <button class="btn btn-success btn-sm w-100" type="submit">Complete request</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </article>
                </div>
            @endforeach
        </div>
    @endif
    <p class="text-muted small mt-3 mb-0" aria-live="polite">Last updated {{ $lastUpdated->format('h:i:s A') }}</p>
</div>
