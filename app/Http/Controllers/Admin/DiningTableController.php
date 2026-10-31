<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Models\Branch;
use App\Models\DiningTable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiningTableController extends Controller
{
    use ScopesBranchData;

    private const STATUSES = ['available', 'occupied', 'reserved', 'maintenance'];

    public function index(Request $request)
    {
        $tables = $this->scopeToAccessibleBranches(DiningTable::query())
            ->with('branch.organization')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('table_number', 'like', "%{$search}%")
                        ->orWhereHas('branch', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $query->where('branch_id', $request->input('branch_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $branches = $this->branchQuery()->with('organization')->orderBy('name')->get();

        return view('admin.dining-tables.index', compact('tables', 'branches'));
    }

    public function create()
    {
        $branches = $this->branchQuery()->with('organization')->orderBy('name')->get();

        return view('admin.dining-tables.create', compact('branches'));
    }

    public function store(Request $request)
    {
        DiningTable::create($this->validatedData($request));

        return redirect()
            ->route('admin.dining-tables.index')
            ->with('success', 'Dining table created successfully.');
    }

    public function show(DiningTable $diningTable)
    {
        $this->ensureBranchAccess($diningTable->branch);
        $diningTable->ensureQrToken();
        $diningTable->load('branch.organization');

        return view('admin.dining-tables.show', ['table' => $diningTable]);
    }

    public function rotateQr(DiningTable $diningTable)
    {
        $this->ensureBranchAccess($diningTable->branch);
        $diningTable->rotateQrToken();

        return redirect()
            ->route('admin.dining-tables.show', $diningTable)
            ->with('success', 'Table QR code replaced. The previous QR code is no longer valid.');
    }

    public function edit(DiningTable $diningTable)
    {
        $this->ensureBranchAccess($diningTable->branch);
        $branches = $this->branchQuery()->with('organization')->orderBy('name')->get();

        return view('admin.dining-tables.edit', [
            'table' => $diningTable,
            'branches' => $branches,
        ]);
    }

    public function update(Request $request, DiningTable $diningTable)
    {
        $this->ensureBranchAccess($diningTable->branch);
        $data = $this->validatedData($request, $diningTable);

        $hasUnsettledOrders = $diningTable->orders()
            ->where(function ($query) {
                $query->whereIn('status', ['pending', 'held', 'preparing', 'ready'])
                    ->orWhere(function ($query) {
                        $query->where('status', 'completed')
                            ->whereNotIn('payment_status', ['paid', 'partially_refunded']);
                    });
            })
            ->exists();

        if ($data['status'] === 'available' && $hasUnsettledOrders) {
            return back()->withErrors(['status' => 'A table with active orders or an outstanding bill cannot be marked available.']);
        }

        $diningTable->update($data);

        return redirect()
            ->route('admin.dining-tables.index')
            ->with('success', 'Dining table updated successfully.');
    }

    public function destroy(DiningTable $diningTable)
    {
        $this->ensureBranchAccess($diningTable->branch);

        if ($diningTable->orders()->exists()) {
            return back()->with('error', 'Dining table cannot be deleted while it has order history.');
        }

        $diningTable->delete();

        return redirect()
            ->route('admin.dining-tables.index')
            ->with('success', 'Dining table deleted successfully.');
    }

    private function validatedData(Request $request, ?DiningTable $diningTable = null): array
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'table_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('dining_tables', 'table_number')
                    ->where('branch_id', $request->input('branch_id'))
                    ->ignore($diningTable),
            ],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        $this->ensureBranchAccess(Branch::findOrFail($data['branch_id']));

        return $data;
    }
}
