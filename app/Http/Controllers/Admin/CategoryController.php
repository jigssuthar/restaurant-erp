<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\ScopesBranchData;
use App\Models\Branch;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    use ScopesBranchData;

    public function index(Request $request)
    {
        $categories = $this->scopeToAccessibleBranches(Category::query())
            ->with('branch.organization')
            ->withCount('products')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhereHas('branch', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('branch.organization', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $query->where('branch_id', $request->input('branch_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->input('status') === 'active');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $branches = $this->branchQuery()->with('organization')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories', 'branches'));
    }

    public function create()
    {
        $branches = $this->branchQuery()->with('organization')->orderBy('name')->get();

        return view('admin.categories.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $this->ensureBranchAccess(Branch::findOrFail($data['branch_id']));
        $data['slug'] = $this->uniqueSlug($data['branch_id'], $data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        $this->ensureBranchAccess($category->branch);
        $category->load('branch.organization')->loadCount('products');

        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $this->ensureBranchAccess($category->branch);
        $branches = $this->branchQuery()->with('organization')->orderBy('name')->get();

        return view('admin.categories.edit', compact('category', 'branches'));
    }

    public function update(Request $request, Category $category)
    {
        $this->ensureBranchAccess($category->branch);
        $data = $this->validatedData($request);
        $this->ensureBranchAccess(Branch::findOrFail($data['branch_id']));
        $data['slug'] = $this->uniqueSlug($data['branch_id'], $data['name'], $category);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $this->ensureBranchAccess($category->branch);

        if ($category->products()->exists()) {
            return back()->with('error', 'Category cannot be deleted while products are assigned to it.');
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
        ]);
    }

    private function uniqueSlug(int|string $branchId, string $name, ?Category $category = null): string
    {
        $baseSlug = Str::slug($name) ?: 'category';
        $slug = $baseSlug;
        $suffix = 2;

        while (Category::where('branch_id', $branchId)
            ->where('slug', $slug)
            ->when($category, fn ($query) => $query->whereKeyNot($category->getKey()))
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
