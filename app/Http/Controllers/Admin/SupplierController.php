<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $organizationId = $this->organizationId($request);
        $suppliers = Supplier::where('organization_id', $organizationId)
            ->withCount('purchaseOrders')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create(Request $request)
    {
        $this->organizationId($request);

        return view('admin.suppliers.form', ['supplier' => new Supplier]);
    }

    public function store(Request $request)
    {
        $organizationId = $this->organizationId($request);
        $supplier = Supplier::create([
            ...$this->validatedData($request),
            'organization_id' => $organizationId,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Supplier created.', 'redirect' => route('admin.suppliers.index')], 201);
        }

        return redirect()->route('admin.suppliers.index')->with('success', "{$supplier->name} added as a supplier.");
    }

    public function edit(Request $request, Supplier $supplier)
    {
        abort_unless($supplier->organization_id === $this->organizationId($request), 404);

        return view('admin.suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        abort_unless($supplier->organization_id === $this->organizationId($request), 404);
        $supplier->update($this->validatedData($request));

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Supplier updated.', 'redirect' => route('admin.suppliers.index')]);
        }

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Request $request, Supplier $supplier)
    {
        abort_unless($supplier->organization_id === $this->organizationId($request), 404);
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error', 'A supplier with purchase order history cannot be deleted. Deactivate it instead.');
        }
        $supplier->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Supplier deleted.', 'refresh' => true]);
        }

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier deleted.');
    }

    private function organizationId(Request $request): int
    {
        abort_if($request->user()->isPlatformAdmin() || ! $request->user()->organization_id, 403);

        return (int) $request->user()->organization_id;
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'tax_reference' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
