@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add Sale</h1>
    <form action="{{ route('sales.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="customer_id" class="form-label">Customer</label>
            <select name="customer_id" id="customer_id" class="form-control" required>
                <option value="">Select Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                @endforeach
            </select>
            @error('customer_id')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        
        <div class="mb-3">
            <label for="category_filter" class="form-label">Category</label>
            <select name="category_filter" id="category_filter" class="form-control">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="mb-3">
            <label for="product_id" class="form-label">Product</label>
            <select name="product_id" id="product_id" class="form-control" required>
                <option value="">Select Product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-stock="{{ $product->quantity }}" data-category="{{ $product->category }}" 
                        {{ old('product_id', $selectedProductId ?? '') == $product->id ? 'selected' : '' }}>
                        {{ $product->name }} ({{ $product->category }} - Stock: {{ $product->quantity }})
                    </option>
                @endforeach
            </select>
            @error('product_id')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control" required value="{{ old('quantity') }}" min="1">
            <small class="form-text text-muted">Available stock: <span id="available-stock">-</span></small>
            @error('quantity')<div class="text-danger">{{ $message }}</div>@enderror
            @error('error')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-success">Create</button>
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
document.getElementById('category_filter').addEventListener('change', function() {
    const selectedCategory = this.value;
    const productSelect = document.getElementById('product_id');
    const options = productSelect.querySelectorAll('option');
    
    options.forEach(option => {
        if (option.value === '') return; // Skip the "Select Product" option
        
        const productCategory = option.getAttribute('data-category');
        if (selectedCategory === '' || productCategory === selectedCategory) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Reset product selection if current selection is hidden
    const selectedOption = productSelect.options[productSelect.selectedIndex];
    if (selectedOption && selectedOption.style.display === 'none') {
        productSelect.value = '';
        document.getElementById('available-stock').textContent = '-';
    }
});

document.getElementById('product_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const stock = selectedOption.getAttribute('data-stock');
    const availableStockSpan = document.getElementById('available-stock');
    
    if (stock) {
        availableStockSpan.textContent = stock;
        document.getElementById('quantity').max = stock;
    } else {
        availableStockSpan.textContent = '-';
        document.getElementById('quantity').removeAttribute('max');
    }
});

// Trigger on page load if product is pre-selected
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    if (productSelect.value) {
        // Auto-select the category for the pre-selected product
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const productCategory = selectedOption.getAttribute('data-category');
        const categoryFilter = document.getElementById('category_filter');
        
        if (productCategory) {
            categoryFilter.value = productCategory;
            categoryFilter.dispatchEvent(new Event('change'));
        }
        
        productSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection 