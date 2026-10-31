<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{
    use ScopesBranchData;

    public function index(Request $request)
    {
        $organizationId = $this->organizationId($request);
        $orders = $this->scopeToAccessibleBranches(PurchaseOrder::query())
            ->where('organization_id', $organizationId)
            ->with(['branch', 'supplier'])
            ->withCount('items')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.purchase-orders.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $organizationId = $this->organizationId($request);
        $branches = $this->branchQuery()->orderBy('name')->get();
        $suppliers = Supplier::where('organization_id', $organizationId)->where('is_active', true)->orderBy('name')->get();
        $items = InventoryItem::whereIn('branch_id', $branches->modelKeys())
            ->where('is_active', true)->with('branch')->orderBy('name')->get();

        return view('admin.purchase-orders.form', compact('branches', 'suppliers', 'items'));
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->ensureBranchAccess($purchaseOrder->branch);
        abort_unless($purchaseOrder->organization_id === $this->organizationId($request), 404);
        $purchaseOrder->load(['branch', 'supplier', 'creator', 'items.inventoryItem']);

        return view('admin.purchase-orders.show', compact('purchaseOrder'));
    }

    public function store(Request $request)
    {
        $organizationId = $this->organizationId($request);
        $data = $request->validate([
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where('organization_id', $organizationId)],
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')->where('organization_id', $organizationId)->where('is_active', true)],
            'expected_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.inventory_item_id' => [
                'required', 'integer', 'distinct',
                Rule::exists('inventory_items', 'id')->where('branch_id', $request->input('branch_id'))->where('is_active', true),
            ],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ]);
        $branch = Branch::findOrFail($data['branch_id']);
        $this->ensureBranchAccess($branch);
        $itemIds = collect($data['items'])->pluck('inventory_item_id');
        $inventory = InventoryItem::where('branch_id', $branch->id)->whereIn('id', $itemIds)->get()->keyBy('id');
        $subtotal = 0;
        $order = DB::transaction(function () use ($data, $organizationId, $branch, $inventory, &$subtotal, $request) {
            $order = PurchaseOrder::create([
                'organization_id' => $organizationId,
                'branch_id' => $branch->id,
                'supplier_id' => $data['supplier_id'],
                'created_by' => $request->user()->id,
                'purchase_order_number' => 'PO-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
                'status' => 'draft',
                'currency_code' => $branch->organization?->settings?->currency_code ?? 'INR',
                'expected_on' => $data['expected_on'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $line) {
                $item = $inventory->get($line['inventory_item_id']);
                $quantity = (float) $line['quantity'];
                $unitCost = (float) $line['unit_cost'];
                $lineTotal = round($quantity * $unitCost, 2);
                $subtotal += $lineTotal;
                $order->items()->create([
                    'inventory_item_id' => $item->id,
                    'item_name' => $item->name,
                    'unit' => $item->unit,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ]);
            }
            $order->update(['subtotal' => $subtotal]);

            return $order;
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Purchase order created.', 'redirect' => route('admin.purchase-orders.index')], 201);
        }

        return redirect()->route('admin.purchase-orders.index')->with('success', "Purchase order {$order->purchase_order_number} created.");
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->ensureBranchAccess($purchaseOrder->branch);
        abort_unless($purchaseOrder->organization_id === $this->organizationId($request), 404);
        $data = $request->validate(['status' => ['required', Rule::in(['ordered', 'received', 'cancelled'])]]);

        DB::transaction(function () use ($purchaseOrder, $data, $request) {
            $order = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            if ($data['status'] === 'ordered' && $order->status === 'draft') {
                $order->update(['status' => 'ordered', 'ordered_at' => now()]);

                return;
            }
            if ($data['status'] === 'cancelled' && in_array($order->status, ['draft', 'ordered'], true)) {
                $order->update(['status' => 'cancelled']);

                return;
            }
            if ($data['status'] !== 'received' || $order->status !== 'ordered') {
                throw ValidationException::withMessages(['status' => 'That purchase order transition is not allowed.']);
            }

            foreach ($order->items as $line) {
                $item = InventoryItem::whereKey($line->inventory_item_id)->lockForUpdate()->firstOrFail();
                $after = (float) $item->quantity_on_hand + (float) $line->quantity;
                $item->update(['quantity_on_hand' => $after]);
                InventoryMovement::create([
                    'inventory_item_id' => $item->id,
                    'created_by' => $request->user()->id,
                    'movement_type' => 'purchase',
                    'quantity' => $line->quantity,
                    'quantity_after' => $after,
                    'reference_type' => 'purchase_order',
                    'reference_id' => $order->id,
                    'reason' => "Received {$order->purchase_order_number}",
                ]);
            }
            $order->update(['status' => 'received', 'received_at' => now()]);
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Purchase order status updated.', 'refresh' => true]);
        }

        return back()->with('success', 'Purchase order status updated.');
    }

    private function organizationId(Request $request): int
    {
        abort_if($request->user()->isPlatformAdmin() || ! $request->user()->organization_id, 403);

        return (int) $request->user()->organization_id;
    }
}
