<?php

namespace App\Http\Controllers;

use App\Actions\CreateRestaurantOrder;
use App\Models\DiningTable;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerTableOrderController extends Controller
{
    public function menu(string $token)
    {
        $table = DiningTable::with(['branch.organization.settings'])
            ->where('qr_token', $token)
            ->firstOrFail();

        abort_unless(\in_array($table->status, ['available', 'occupied'], true), 404);

        $products = Product::with('category')
            ->where('branch_id', $table->branch_id)
            ->where('is_available', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get();

        return view('customer.table-menu', compact('table', 'products'));
    }

    public function store(Request $request, string $token, CreateRestaurantOrder $createOrder)
    {
        $table = DiningTable::where('qr_token', $token)->firstOrFail();

        abort_unless(\in_array($table->status, ['available', 'occupied'], true), 404);

        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')
                    ->where('branch_id', $table->branch_id)
                    ->where('is_available', true),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $order = $createOrder->handle(
            branch: $table->branch,
            items: $data['items'],
            orderType: 'dine_in',
            diningTable: $table,
            details: $data,
            allowOccupiedTable: true,
        );
        $order->load('items');

        return redirect()
            ->route('qr.menu', ['token' => $token])
            ->with('qr_order_confirmation', [
                'table_id' => $table->id,
                'order_number' => $order->order_number,
                'created_at' => $order->created_at->format('d M Y, h:i A'),
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'currency_code' => $order->currency_code,
                'customer_name' => $order->customer_name,
                'notes' => $order->notes,
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ])->all(),
                'subtotal' => $order->subtotal,
                'tax_label' => $order->tax_label ?: 'Tax',
                'tax_amount' => $order->tax_amount,
                'service_charge' => $order->service_charge,
                'delivery_fee' => $order->delivery_fee,
                'total' => $order->total,
            ]);
    }
}
