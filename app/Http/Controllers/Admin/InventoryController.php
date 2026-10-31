<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    use ScopesBranchData;

    public function index(Request $request)
    {
        $items = $this->scopeToAccessibleBranches(InventoryItem::query())
            ->with('branch')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%"));
            })
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
            ->when($request->boolean('low_stock'), fn ($query) => $query->whereColumn('quantity_on_hand', '<=', 'reorder_level'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
        $branches = $this->branchQuery()->orderBy('name')->get(['id', 'name']);

        return view('admin.inventory.index', compact('items', 'branches'));
    }

    public function create()
    {
        return view('admin.inventory.form', ['item' => new InventoryItem, 'branches' => $this->branchQuery()->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $this->ensureBranchAccess(Branch::findOrFail($data['branch_id']));
        $initialQuantity = $data['quantity_on_hand'];
        $item = DB::transaction(function () use ($data, $initialQuantity, $request) {
            $item = InventoryItem::create($data);
            if ((float) $initialQuantity > 0) {
                $item->movements()->create([
                    'created_by' => $request->user()->id,
                    'movement_type' => 'opening',
                    'quantity' => $initialQuantity,
                    'quantity_after' => $initialQuantity,
                    'reason' => 'Opening stock',
                ]);
            }

            return $item;
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Inventory item created.', 'redirect' => route('admin.inventory.index')], 201);
        }

        return redirect()->route('admin.inventory.index')->with('success', "{$item->name} added to inventory.");
    }

    public function edit(InventoryItem $inventory)
    {
        $this->ensureBranchAccess($inventory->branch);

        return view('admin.inventory.form', ['item' => $inventory, 'branches' => $this->branchQuery()->orderBy('name')->get()]);
    }

    public function update(Request $request, InventoryItem $inventory)
    {
        $this->ensureBranchAccess($inventory->branch);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('inventory_items', 'sku')
                ->where('branch_id', $inventory->branch_id)->ignore($inventory->id)],
            'unit' => ['required', 'string', 'max:30'],
            'reorder_level' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
        $inventory->update($data);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Inventory item updated.', 'redirect' => route('admin.inventory.index')]);
        }

        return redirect()->route('admin.inventory.index')->with('success', 'Inventory item updated.');
    }

    public function movements(InventoryItem $inventory)
    {
        $this->ensureBranchAccess($inventory->branch);
        $movements = $inventory->movements()
            ->with('creator')
            ->latest()
            ->paginate(25);

        return view('admin.inventory.movements', compact('inventory', 'movements'));
    }

    public function adjust(Request $request, InventoryItem $inventory)
    {
        $this->ensureBranchAccess($inventory->branch);
        $data = $request->validate([
            'movement_type' => ['required', Rule::in(['in', 'out'])],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($inventory, $data, $request) {
            $locked = InventoryItem::whereKey($inventory->id)->lockForUpdate()->firstOrFail();
            $delta = $data['movement_type'] === 'in' ? (float) $data['quantity'] : -((float) $data['quantity']);
            $after = (float) $locked->quantity_on_hand + $delta;
            if ($after < 0) {
                throw ValidationException::withMessages(['quantity' => 'Stock cannot be reduced below zero.']);
            }
            $locked->update(['quantity_on_hand' => $after]);
            $locked->movements()->create([
                'created_by' => $request->user()->id,
                'movement_type' => $data['movement_type'],
                'quantity' => $data['quantity'],
                'quantity_after' => $after,
                'reason' => $data['reason'],
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Stock adjusted.', 'quantity' => (float) $inventory->fresh()->quantity_on_hand]);
        }

        return back()->with('success', 'Stock adjusted successfully.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('inventory_items', 'sku')
                ->where('branch_id', $request->input('branch_id'))],
            'unit' => ['required', 'string', 'max:30'],
            'quantity_on_hand' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'reorder_level' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'unit_cost' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
