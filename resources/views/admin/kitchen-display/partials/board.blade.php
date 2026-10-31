<div class="kds-board-content">
    <div class="row g-2 mb-3">
        @foreach(['pending' => 'New', 'preparing' => 'Preparing', 'ready' => 'Ready', 'completed' => 'Served today'] as $status => $label)
            <div class="col-6 col-lg-3"><div class="admin-card p-3 mb-0 d-flex justify-content-between align-items-center"><span>{{ $label }}</span><strong>{{ $statusCounts[$status] ?? 0 }}</strong></div></div>
        @endforeach
    </div>
    <div class="kds-board">
        @foreach(['pending' => 'New orders', 'preparing' => 'Preparing', 'ready' => 'Ready to serve', 'completed' => 'Served today'] as $status => $label)
            <section class="kds-lane" aria-label="{{ $label }}">
                <div class="kds-lane-head"><span>{{ $label }}</span><span class="kds-count">{{ ($ordersByStatus[$status] ?? collect())->count() }}</span></div>
                @forelse($ordersByStatus[$status] ?? collect() as $order)
                    <article class="kds-ticket" data-status="{{ $status }}">
                        <div class="d-flex justify-content-between gap-2">
                            <strong>{{ $order->order_number }}</strong>
                            <span class="small text-muted">{{ $order->created_at->format('h:i A') }}</span>
                        </div>
                        <div class="small text-muted mt-1">
                            {{ $order->diningTable ? 'Table '.$order->diningTable->table_number : ucfirst(str_replace('_', ' ', $order->order_type)) }}
                            @if($order->customer_name) · {{ $order->customer_name }} @endif
                        </div>
                        <ul class="kds-items">
                            @foreach($order->items as $item)
                                <li class="d-flex justify-content-between gap-2"><span>{{ $item->product_name }}</span><strong>× {{ $item->quantity }}</strong></li>
                            @endforeach
                        </ul>
                        @if($order->notes)<p class="small bg-warning-subtle rounded p-2 mb-2"><strong>Note:</strong> {{ $order->notes }}</p>@endif
                        @if($status === 'pending')
                            <form action="{{ route('kitchen-display.orders.status', $order) }}" method="POST" data-ajax-form>
                                @csrf @method('PATCH')<input type="hidden" name="status" value="preparing">
                                <button class="btn btn-primary btn-sm w-100" type="submit"><i class="bi bi-play-fill me-1"></i>Start preparing</button>
                            </form>
                        @elseif($status === 'preparing')
                            <form action="{{ route('kitchen-display.orders.status', $order) }}" method="POST" data-ajax-form>
                                @csrf @method('PATCH')<input type="hidden" name="status" value="ready">
                                <button class="btn btn-success btn-sm w-100" type="submit"><i class="bi bi-check2 me-1"></i>Mark ready</button>
                            </form>
                        @elseif($status === 'ready')
                            <span class="badge text-bg-success">Waiting for service</span>
                        @else
                            <span class="badge text-bg-light">Served</span>
                        @endif
                    </article>
                @empty
                    <p class="small text-muted text-center py-3 mb-0">No tickets in this lane.</p>
                @endforelse
            </section>
        @endforeach
    </div>
    <p class="text-muted small mt-3 mb-0" aria-live="polite">Last updated {{ $lastUpdated->format('h:i:s A') }}</p>
</div>
