@if($branches->isEmpty())
    <div class="alert alert-warning">Create a branch before adding products.
        <a href="{{ route('admin.branches.create') }}" class="alert-link">Add branch</a></div>
@endif
<div class="row g-3">
    <div class="col-md-6">
        <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
        <select id="branch_id" name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
            <option value="">Select branch</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" data-currency-code="{{ $branch->organization->settings?->currency_code ?? 'INR' }}"
                    @selected((string) old('branch_id', $product->branch_id ?? '') === (string) $branch->id)>
                    {{ $branch->organization->name }} — {{ $branch->name }}</option>
            @endforeach
        </select>
        @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">Select category</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" data-branch-id="{{ $category->branch_id }}"
                    @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>
                    {{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="name" class="form-label">Product name <span class="text-danger">*</span></label>
        <input id="name" name="name" value="{{ old('name', $product->name ?? '') }}" maxlength="255" required
               class="form-control @error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="sku" class="form-label">SKU</label>
        <input id="sku" name="sku" value="{{ old('sku', $product->sku ?? '') }}" maxlength="255"
               class="form-control @error('sku') is-invalid @enderror">
        @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="price" class="form-label">Selling price (<span id="currencyCode">INR</span>) <span class="text-danger">*</span></label>
        <input id="price" type="number" name="price" value="{{ old('price', $product->price ?? '') }}" min="0" step="0.01" required
               class="form-control @error('price') is-invalid @enderror">
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="cost_price" class="form-label">Cost price (<span class="currency-code">INR</span>) <span class="text-danger">*</span></label>
        <input id="cost_price" type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? '') }}" min="0" step="0.01" required
               class="form-control @error('cost_price') is-invalid @enderror">
        @error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="type" class="form-label">Product type <span class="text-danger">*</span></label>
        <input id="type" name="type" value="{{ old('type', $product->type ?? 'food') }}" maxlength="50" required
               class="form-control @error('type') is-invalid @enderror">
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="is_available" class="form-label">Availability <span class="text-danger">*</span></label>
        <select id="is_available" name="is_available" class="form-select @error('is_available') is-invalid @enderror" required>
            <option value="1" @selected((string) old('is_available', $product->is_available ?? '1') === '1')>Available</option>
            <option value="0" @selected((string) old('is_available', $product->is_available ?? '1') === '0')>Unavailable</option>
        </select>
        @error('is_available')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="image" class="form-label">Product image</label>
        <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
               class="form-control @error('image') is-invalid @enderror">
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if(isset($product) && $product->image)
            <img class="mt-2 rounded" src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" style="max-width:120px">
        @endif
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="3" maxlength="5000"
                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-orange" @disabled($branches->isEmpty())>Save Product</button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-light border">Cancel</a>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchSelect = document.getElementById('branch_id');
    const categorySelect = document.getElementById('category_id');
    const currencyCode = document.getElementById('currencyCode');
    const updateCurrency = () => {
        const code = branchSelect.selectedOptions[0]?.dataset.currencyCode || 'INR';
        currencyCode.textContent = code;
        document.querySelectorAll('.currency-code').forEach((label) => {
            label.textContent = code;
        });
    };
    const updateCategories = () => {
        const branchId = branchSelect.value;
        Array.from(categorySelect.options).forEach((option) => {
            if (option.value) {
                option.hidden = option.dataset.branchId !== branchId;
                if (option.hidden && option.selected) categorySelect.value = '';
            }
        });
    };
    branchSelect.addEventListener('change', updateCategories);
    branchSelect.addEventListener('change', updateCurrency);
    updateCategories();
    updateCurrency();
});
</script>
@endpush
