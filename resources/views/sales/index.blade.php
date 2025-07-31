@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Sales</h1>
    <a href="{{ route('sales.create') }}" class="btn btn-primary mb-3">Add Sale</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Search Section -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">🔍 Search Sales</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('sales.index') }}" class="row g-3">
                <div class="col-md-8">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by customer name, product name, or quantity...">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Clear Search</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"> Filter Options</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('sales.index') }}" class="row g-3">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="direction" value="{{ request('direction') }}">
                <div class="col-md-3">
                    <label for="customer_id" class="form-label">Filter by Customer</label>
                    <select class="form-control" id="customer_id" name="customer_id">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="product_id" class="form-label">Filter by Product</label>
                    <select class="form-control" id="product_id" name="product_id">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="min_quantity" class="form-label">Min Quantity</label>
                    <input type="number" class="form-control" id="min_quantity" name="min_quantity" value="{{ request('min_quantity') }}" placeholder="1">
                </div>
                <div class="col-md-3">
                    <label for="max_quantity" class="form-label">Max Quantity</label>
                    <input type="number" class="form-control" id="max_quantity" name="max_quantity" value="{{ request('max_quantity') }}" placeholder="100">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-outline-primary">Apply Filters</button>
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Clear Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0">
            Showing {{ $sales->count() }} sales
        </p>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th><a href="{{ route('sales.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">ID</a></th>
                <th><a href="{{ route('sales.index', array_merge(request()->query(), ['sort' => 'customer_name', 'direction' => request('sort') == 'customer_name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">Customer</a></th>
                <th><a href="{{ route('sales.index', array_merge(request()->query(), ['sort' => 'product_name', 'direction' => request('sort') == 'product_name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">Product</a></th>
                <th><a href="{{ route('sales.index', array_merge(request()->query(), ['sort' => 'quantity', 'direction' => request('sort') == 'quantity' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">Quantity</a></th>
                <th><a href="{{ route('sales.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">Created At</a></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->customer->name ?? '-' }}</td>
                    <td>{{ $sale->product->name ?? '-' }}</td>
                    <td>{{ $sale->quantity }}</td>
                    <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('sales.show', $sale) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('sales.destroy', $sale) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No sales found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection