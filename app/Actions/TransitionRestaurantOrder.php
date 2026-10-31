<?php

namespace App\Actions;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionRestaurantOrder
{
    private const TRANSITIONS = [
        'pending' => ['held', 'preparing', 'cancelled'],
        'held' => ['pending', 'cancelled'],
        'preparing' => ['ready', 'cancelled'],
        'ready' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function handle(Order $order, string $nextStatus): Order
    {
        return DB::transaction(function () use ($order, $nextStatus) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! \in_array($nextStatus, self::TRANSITIONS[$lockedOrder->status] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => 'That order status transition is not allowed.',
                ]);
            }

            $lockedOrder->update(['status' => $nextStatus]);

            if (\in_array($nextStatus, ['completed', 'cancelled'], true)) {
                app(ReleaseDiningTableWhenSettled::class)->handle($lockedOrder);
            }

            return $lockedOrder->refresh();
        });
    }
}
