<?php

namespace App\Actions;

use App\Models\DiningTable;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class ReleaseDiningTableWhenSettled
{
    public function handle(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! \in_array($lockedOrder->status, ['completed', 'cancelled'], true)
                || ! $lockedOrder->dining_table_id) {
                return;
            }

            $table = DiningTable::query()
                ->whereKey($lockedOrder->dining_table_id)
                ->lockForUpdate()
                ->first();

            if (! $table) {
                return;
            }

            $hasOpenOrders = Order::query()
                ->where('dining_table_id', $table->id)
                ->where(function ($query) {
                    $query->whereIn('status', ['pending', 'held', 'preparing', 'ready'])
                        ->orWhere(function ($query) {
                            $query->where('status', 'completed')
                                ->whereNotIn('payment_status', ['paid', 'partially_refunded']);
                        });
                })
                ->exists();

            if (! $hasOpenOrders && $table->status === 'occupied') {
                $table->update(['status' => 'available']);
            }
        });
    }
}
