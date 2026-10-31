<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateRestaurantOrder;
use App\Actions\ReleaseDiningTableWhenSettled;
use App\Actions\TransitionRestaurantOrder;
use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashRegisterSession;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderPayment;
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
        $order->load(['branch.organization.settings', 'diningTable', 'user', 'items', 'payments.creator']);
        $paidAmount = (float) $order->payments->where('transaction_type', 'payment')->sum('amount');
        $refundedAmount = (float) $order->payments->where('transaction_type', 'refund')->sum('amount');
        $remainingBalance = max(0, round((float) $order->total - $paidAmount, 2));
        $refundableAmount = max(0, round($paidAmount - $refundedAmount, 2));

        return view('admin.orders.show', compact('order', 'paidAmount', 'refundedAmount', 'remainingBalance', 'refundableAmount'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->ensureBranchAccess($order->branch);
        $data = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ]);
        if ($data['status'] === 'cancelled' && $order->payments()->where('transaction_type', 'payment')->exists()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Paid orders cannot be cancelled without a refund workflow.',
                    'errors' => ['status' => ['Paid orders cannot be cancelled without a refund workflow.']],
                ], 422);
            }

            return back()->withErrors(['status' => 'Paid orders cannot be cancelled without a refund workflow.']);
        }

        app(TransitionRestaurantOrder::class)->handle($order, $data['status']);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Order status updated.', 'refresh' => true]);
        }

        return back()->with('success', 'Order status updated successfully.');
    }

    public function markPaid(Request $request, Order $order)
    {
        $this->ensureBranchAccess($order->branch);
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(Order::PAYMENT_METHODS)],
        ]);

        $remaining = $this->remainingBalance($order);
        abort_if($remaining <= 0, 422, 'This order has no outstanding balance.');

        $this->storePaymentTransactions($request, $order, [[
            'payment_method' => $data['payment_method'],
            'amount' => $remaining,
        ]]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Payment recorded.', 'refresh' => true]);
        }

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function recordPayments(Request $request, Order $order)
    {
        $this->ensureBranchAccess($order->branch);
        $data = $request->validate([
            'payments' => ['required', 'array', 'min:1', 'max:5'],
            'payments.*.payment_method' => ['required', Rule::in(Order::PAYMENT_METHODS)],
            'payments.*.amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:100000000'],
            'payments.*.reference' => ['nullable', 'string', 'max:120'],
        ]);
        $this->storePaymentTransactions($request, $order, $data['payments']);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Payment recorded.', 'refresh' => true]);
        }

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function refund(Request $request, Order $order)
    {
        $this->ensureBranchAccess($order->branch);
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(Order::PAYMENT_METHODS)],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:100000000'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $order, $data) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $received = (float) $lockedOrder->payments()->where('transaction_type', 'payment')->sum('amount');
            $refunded = (float) $lockedOrder->payments()->where('transaction_type', 'refund')->sum('amount');
            $available = round($received - $refunded, 2);
            $amount = round((float) $data['amount'], 2);

            if ($amount > $available) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => 'Refund cannot exceed the remaining amount paid.',
                ]);
            }

            OrderPayment::create([
                'order_id' => $lockedOrder->id,
                'cash_register_session_id' => $data['payment_method'] === 'cash'
                    ? CashRegisterSession::query()
                        ->where('branch_id', $lockedOrder->branch_id)
                        ->where('opened_by', $request->user()->id)
                        ->whereNull('closed_at')
                        ->lockForUpdate()
                        ->latest('opened_at')
                        ->value('id')
                    : null,
                'created_by' => $request->user()->id,
                'transaction_type' => 'refund',
                'payment_method' => $data['payment_method'],
                'amount' => $amount,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'],
                'recorded_at' => now(),
            ]);
            $this->syncPaymentSummary($lockedOrder);
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Refund recorded.', 'refresh' => true]);
        }

        return back()->with('success', 'Refund recorded successfully.');
    }

    private function storePaymentTransactions(Request $request, Order $order, array $payments): void
    {
        DB::transaction(function () use ($request, $order, $payments) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status === 'cancelled') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'payments' => 'Cancelled orders cannot receive payments.',
                ]);
            }
            $remaining = $this->remainingBalance($lockedOrder);
            $amount = round(collect($payments)->sum(fn ($payment) => (float) $payment['amount']), 2);
            if ($amount > $remaining) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'payments' => 'Payment exceeds the outstanding order balance.',
                ]);
            }

            foreach ($payments as $payment) {
                $registerSession = $payment['payment_method'] === 'cash'
                    ? CashRegisterSession::query()
                        ->where('branch_id', $lockedOrder->branch_id)
                        ->where('opened_by', $request->user()->id)
                        ->whereNull('closed_at')
                        ->lockForUpdate()
                        ->latest('opened_at')
                        ->first()
                    : null;
                OrderPayment::create([
                    'order_id' => $lockedOrder->id,
                    'cash_register_session_id' => $registerSession?->id,
                    'created_by' => $request->user()->id,
                    'transaction_type' => 'payment',
                    'payment_method' => $payment['payment_method'],
                    'amount' => round((float) $payment['amount'], 2),
                    'reference' => $payment['reference'] ?? null,
                    'recorded_at' => now(),
                ]);
            }

            $this->syncPaymentSummary($lockedOrder);
        });

        if ($order->fresh()->status === 'completed') {
            app(ReleaseDiningTableWhenSettled::class)->handle($order);
        }
    }

    private function syncPaymentSummary(Order $order): void
    {
        $received = (float) $order->payments()->where('transaction_type', 'payment')->sum('amount');
        $refunded = (float) $order->payments()->where('transaction_type', 'refund')->sum('amount');
        $netPaid = round($received - $refunded, 2);
        $refundStatus = $refunded > 0;
        $paymentStatus = match (true) {
            $refundStatus && $netPaid <= 0 => 'refunded',
            $refundStatus => 'partially_refunded',
            $netPaid <= 0 => 'pending',
            $netPaid < (float) $order->total => 'partial',
            default => 'paid',
        };
        $methods = $order->payments()
            ->where('transaction_type', 'payment')
            ->distinct()
            ->pluck('payment_method')
            ->values();

        $order->update([
            'payment_status' => $paymentStatus,
            'payment_method' => $methods->count() === 1 ? $methods->first() : ($methods->isNotEmpty() ? 'multiple' : null),
            'paid_at' => $received >= (float) $order->total || $order->payment_status === 'paid'
                ? ($order->paid_at ?? now())
                : null,
            'invoice_number' => $order->invoice_number ?: sprintf('INV-%05d-%08d', $order->branch_id, $order->id),
        ]);
    }

    private function remainingBalance(Order $order): float
    {
        $received = (float) $order->payments()->where('transaction_type', 'payment')->sum('amount');
        if ($order->payment_status === 'paid' && $received === 0.0) {
            return 0;
        }

        return max(0, round((float) $order->total - $received, 2));
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
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
            'discount_type' => ['nullable', 'required_with:discount_value', Rule::in(['fixed', 'percentage'])],
            'discount_value' => ['nullable', 'required_with:discount_type', 'numeric', 'min:0', 'max:100000000'],
            'hold_order' => ['sometimes', 'boolean'],
        ]);
        if (($data['discount_type'] ?? null) === 'percentage' && (float) ($data['discount_value'] ?? 0) > 100) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'discount_value' => 'Percentage discount cannot exceed 100%.',
            ]);
        }
        if ($request->boolean('hold_order') && $data['payment_status'] === 'paid') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'hold_order' => 'A held order cannot be marked paid during creation.',
            ]);
        }
        $data['hold_order'] = $request->boolean('hold_order');

        return $data;
    }
}
