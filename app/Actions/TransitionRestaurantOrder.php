<?php

namespace App\Actions;

use App\Models\Order;
use App\Services\FirebasePush;
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
        $updatedOrder = DB::transaction(function () use ($order, $nextStatus) {
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

        if ($nextStatus === 'pending') {
            app(FirebasePush::class)->notifyBranch(
                $updatedOrder->branch_id,
                ['admin', 'manager', 'staff', 'waiter', 'captain', 'chef', 'kitchen_manager'],
                'New order received',
                "Order {$updatedOrder->order_number} is waiting for preparation.",
                route('kitchen-display.index', ['branch_id' => $updatedOrder->branch_id]),
            );
        } elseif ($nextStatus === 'ready') {
            app(FirebasePush::class)->notifyBranch(
                $updatedOrder->branch_id,
                ['admin', 'manager', 'staff', 'waiter', 'captain'],
                'Order ready to serve',
                "Order {$updatedOrder->order_number} is ready.",
                route('service-app.index', ['branch_id' => $updatedOrder->branch_id]),
            );
        } elseif ($nextStatus === 'cancelled') {
            app(FirebasePush::class)->notifyBranch(
                $updatedOrder->branch_id,
                ['admin', 'manager'],
                'Order cancelled',
                "Order {$updatedOrder->order_number} was cancelled.",
                route('admin.orders.index', ['branch_id' => $updatedOrder->branch_id]),
            );
        }

        return $updatedOrder;
    }
}
