<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use ScopesBranchData;

    public function index(Request $request)
    {
        $products = $this->scopeToAccessibleBranches(Product::query())
            ->with(['branch.organization.settings', 'category'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $query->where('branch_id', $request->input('branch_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_available', $request->input('status') === 'available');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $branches = $this->branchQuery()->with('organization')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'branches'));
    }

    public function create()
    {
        [$branches, $categories] = $this->formOptions();

        return view('admin.products.create', compact('branches', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $this->ensureBranchAccess($product->branch);
        $product->load(['branch.organization.settings', 'category']);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $this->ensureBranchAccess($product->branch);
        [$branches, $categories] = $this->formOptions();

        return view('admin.products.edit', compact('product', 'branches', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $this->ensureBranchAccess($product->branch);
        $data = $this->validatedData($request, $product);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->ensureBranchAccess($product->branch);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            $this->branchQuery()->with('organization.settings')->orderBy('name')->get(),
            $this->scopeToAccessibleBranches(Category::query())
                ->orderBy('name')
                ->get(['id', 'branch_id', 'name']),
        ];
    }

    private function validatedData(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('branch_id', $request->input('branch_id')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'sku')
                    ->where('branch_id', $request->input('branch_id'))
                    ->ignore($product),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'cost_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'type' => ['required', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_available' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
        ]);

        $this->ensureBranchAccess(Branch::findOrFail($data['branch_id']));

        return $data;
    }
}
