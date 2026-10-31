<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateRestaurantOrder;
use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    use ScopesBranchData;

    public function index(Request $request)
    {
        $orders = $this->scopeToAccessibleBranches(Order::query())
            ->with(['branch', 'diningTable'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $query->where('branch_id', $request->input('branch_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('order_type'), function ($query) use ($request) {
                $query->where('order_type', $request->input('order_type'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $branches = $this->branchQuery()->orderBy('name')->get(['id', 'name']);

        return view('admin.orders.index', compact('orders', 'branches'));
    }

    public function create()
    {
        $branches = $this->branchQuery()->with('organization.settings')->orderBy('name')->get();
        $products = $this->scopeToAccessibleBranches(Product::query())
            ->with('branch.organization.settings')
            ->where('is_available', true)
            ->orderBy('name')
            ->get(['id', 'branch_id', 'name', 'price']);
        $tables = $this->scopeToAccessibleBranches(DiningTable::query())
            ->where('status', 'available')
            ->orderBy('table_number')
            ->get(['id', 'branch_id', 'table_number', 'capacity']);

        return view('admin.orders.create', compact('branches', 'products', 'tables'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $branch = Branch::findOrFail($data['branch_id']);
        $this->ensureBranchAccess($branch);

        $tableId = $data['dining_table_id'] ?? null;
        $table = $tableId ? DiningTable::findOrFail($tableId) : null;
        if ($table) {
            $this->ensureBranchAccess($table->branch);
        }

        $order = app(CreateRestaurantOrder::class)->handle(
            branch: $branch,
            items: $data['items'],
            orderType: $data['order_type'],
            diningTable: $table,
            user: $request->user(),
            details: $data,
        );

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order created successfully.');
    }

    public function show(Order $order)
    {
        $this->ensureBranchAccess($order->branch);
        $order->load(['branch.organization.settings', 'diningTable', 'user', 'items']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->ensureBranchAccess($order->branch);
        $data = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ]);

        $allowedTransitions = [
            'pending' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (! \in_array($data['status'], $allowedTransitions[$order->status] ?? [], true)) {
            return back()->withErrors(['status' => 'That order status transition is not allowed.']);
        }

        if ($data['status'] === 'completed' && $order->payment_status !== 'paid') {
            return back()->withErrors(['status' => 'Record payment before completing the order.']);
        }

        if ($data['status'] === 'cancelled' && $order->payment_status === 'paid') {
            return back()->withErrors(['status' => 'Paid orders cannot be cancelled without a refund workflow.']);
        }

        DB::transaction(function () use ($data, $order) {
            $order->update(['status' => $data['status']]);

            if (\in_array($data['status'], ['completed', 'cancelled'], true)
                && $order->dining_table_id) {
                $hasOtherActiveOrders = Order::where('dining_table_id', $order->dining_table_id)
                    ->whereIn('status', ['pending', 'preparing', 'ready'])
                    ->exists();

                if (! $hasOtherActiveOrders) {
                    DiningTable::whereKey($order->dining_table_id)
                        ->where('status', 'occupied')
                        ->update(['status' => 'available']);
                }
            }
        });

        return back()->with('success', 'Order status updated successfully.');
    }

    public function markPaid(Request $request, Order $order)
    {
        $this->ensureBranchAccess($order->branch);
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(Order::PAYMENT_METHODS)],
        ]);

        if ($order->payment_status === 'paid' || $order->status === 'cancelled') {
            return back()->withErrors(['payment_method' => 'Payment cannot be recorded for this order.']);
        }

        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $data['payment_method'],
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Payment recorded successfully.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'order_type' => ['required', Rule::in(Order::TYPES)],
            'dining_table_id' => [
                'nullable',
                'required_if:order_type,dine_in',
                'prohibited_unless:order_type,dine_in',
                'integer',
                Rule::exists('dining_tables', 'id')
                    ->where('branch_id', $request->input('branch_id'))
                    ->where('status', 'available'),
            ],
            'customer_name' => ['nullable', 'required_if:order_type,delivery', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'delivery_address' => ['nullable', 'required_if:order_type,delivery', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')
                    ->where('branch_id', $request->input('branch_id'))
                    ->where('is_available', true),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'payment_status' => ['required', Rule::in(['pending', 'paid'])],
            'payment_method' => [
                'nullable',
                'required_if:payment_status,paid',
                Rule::in(Order::PAYMENT_METHODS),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
