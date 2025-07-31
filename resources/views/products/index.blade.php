@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Products</h1>
    <a href="{{ route('products.create') }}" class="btn btn-primary mb-3">Add Product</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Search Section -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">🔍 Search Products</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('products.index') }}" class="row g-3">
                <div class="col-md-8">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by product name or category...">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">Clear Search</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">🎯 Filter Options</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('products.index') }}" class="row g-3">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="direction" value="{{ request('direction') }}">
                <div class="col-md-3">
                    <label for="category" class="form-label">Filter by Category</label>
                    <select class="form-control" id="category" name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="stock_level" class="form-label">Filter by Stock Level</label>
                    <select class="form-control" id="stock_level" name="stock_level">
                        <option value="">All Stock</option>
                        <option value="in_stock" {{ request('stock_level') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('stock_level') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('stock_level') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="min_price" class="form-label">Min Price</label>
                    <input type="number" step="0.01" class="form-control" id="min_price" name="min_price" value="{{ request('min_price') }}" placeholder="0.00">
                </div>
                <div class="col-md-3">
                    <label for="max_price" class="form-label">Max Price</label>
                    <input type="number" step="0.01" class="form-control" id="max_price" name="max_price" value="{{ request('max_price') }}" placeholder="999.99">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-outline-primary">Apply Filters</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Clear Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0">
            Showing {{ $products->count() }} products
        </p>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th><a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">ID</a></th>
                <th><a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">Name</a></th>
                <th><a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'category', 'direction' => request('sort') == 'category' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">Category</a></th>
                <th><a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'price', 'direction' => request('sort') == 'price' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">Price</a></th>
                <th><a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'quantity', 'direction' => request('sort') == 'quantity' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">Quantity</a></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category }}</td>
                    <td>${{ number_format($product->price, 2) }}</td>
                    <td>
                        <span class="badge {{ $product->quantity > 10 ? 'bg-success' : ($product->quantity > 0 ? 'bg-warning' : 'bg-danger') }}">
                            {{ $product->quantity }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection