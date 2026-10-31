<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrganizationSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateRestaurantOrder
{
    public function handle(
        Branch $branch,
        array $items,
        string $orderType,
        ?DiningTable $diningTable = null,
        ?User $user = null,
        array $details = [],
        bool $allowOccupiedTable = false,
    ): Order {
        return DB::transaction(function () use ($branch, $items, $orderType, $diningTable, $user, $details, $allowOccupiedTable) {
            $products = $this->availableProducts($branch, $items);
            $table = $diningTable
                ? DiningTable::whereKey($diningTable->getKey())->lockForUpdate()->firstOrFail()
                : null;

            if ($table && (
                $table->branch_id !== $branch->id
                || ! \in_array($table->status, $allowOccupiedTable ? ['available', 'occupied'] : ['available'], true)
            )) {
                throw ValidationException::withMessages([
                    'dining_table_id' => 'That table is not available for a new order.',
                ]);
            }

            $settings = OrganizationSetting::firstOrNew(
                ['organization_id' => $branch->organization_id],
                [
                    'currency_code' => 'INR',
                    'tax_rate' => 0,
                    'service_charge_rate' => 0,
                    'delivery_fee' => 0,
                ]
            );

            $subtotal = 0;
            $lineItems = [];

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $unitPrice = (float) $product->price;
                $lineTotal = round($unitPrice * (int) $item['quantity'], 2);
                $subtotal = round($subtotal + $lineTotal, 2);
                $lineItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $taxRate = (float) $settings->tax_rate;
            $serviceChargeRate = (float) $settings->service_charge_rate;
            $taxAmount = round($subtotal * $taxRate / 100, 2);
            $serviceCharge = round($subtotal * $serviceChargeRate / 100, 2);
            $deliveryFee = $orderType === 'delivery' ? (float) $settings->delivery_fee : 0;
            $total = round($subtotal + $taxAmount + $serviceCharge + $deliveryFee, 2);
            $paymentStatus = $details['payment_status'] ?? 'pending';

            $order = Order::create([
                'branch_id' => $branch->id,
                'dining_table_id' => $table?->id,
                'user_id' => $user?->id,
                'order_number' => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'order_type' => $orderType,
                'status' => 'pending',
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentStatus === 'paid' ? ($details['payment_method'] ?? null) : null,
                'currency_code' => $settings->currency_code ?: 'INR',
                'tax_label' => $settings->tax_label,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'service_charge_rate' => $serviceChargeRate,
                'service_charge' => $serviceCharge,
                'delivery_fee' => $deliveryFee,
                'customer_name' => $details['customer_name'] ?? null,
                'customer_phone' => $details['customer_phone'] ?? null,
                'delivery_address' => $details['delivery_address'] ?? null,
                'notes' => $details['notes'] ?? null,
                'paid_at' => $paymentStatus === 'paid' ? now() : null,
                'subtotal' => $subtotal,
                'total' => $total,
            ]);

            $order->items()->createMany($lineItems);
            $table?->update(['status' => 'occupied']);

            return $order;
        });
    }

    private function availableProducts(Branch $branch, array $items): Collection
    {
        $productIds = collect($items)->pluck('product_id')->unique();
        $products = Product::query()
            ->where('branch_id', $branch->id)
            ->where('is_available', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->whereIn('id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($products->count() !== $productIds->count()) {
            throw ValidationException::withMessages([
                'items' => 'Each selected product must be available at the selected branch.',
            ]);
        }

        return $products;
    }
}
