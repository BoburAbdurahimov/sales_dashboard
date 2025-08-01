@extends('layouts.app')

@section('content')
<style>
.sortable-header {
    cursor: pointer;
    transition: color 0.2s ease;
}
.sortable-header:hover {
    color: #0d6efd !important;
}
.sortable-header i {
    font-size: 0.8em;
    margin-left: 4px;
}

/* Pagination styling */
.pagination {
    margin-bottom: 0;
    gap: 2px;
}
.page-link {
    color: #6c757d;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 8px 12px;
    font-weight: 500;
    transition: all 0.2s ease;
}
.page-link:hover {
    color: #0d6efd;
    background-color: #f8f9fa;
    border-color: #0d6efd;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
}
.page-item.disabled .page-link {
    color: #adb5bd;
    background-color: #f8f9fa;
    border-color: #dee2e6;
}
</style>
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
        <div>
            @if($sales->total() > 0)
            <div class="d-flex align-items-center">
                <span class="badge bg-primary me-2">{{ $sales->total() }}</span>
                <span class="text-muted">sales found</span>
                @if($sales->hasPages())
                <span class="text-muted ms-3">
                    Page {{ $sales->currentPage() }} of {{ $sales->lastPage() }}
                </span>
                @endif
            </div>
            @endif
            @if(request('sort'))
            <small class="text-muted">
                <i class="bi bi-sort-down"></i> 
                Sorted by: <strong>{{ ucfirst(str_replace('_', ' ', request('sort'))) }}</strong> 
                ({{ request('direction') == 'asc' ? 'Ascending' : 'Descending' }})
            </small>
            @endif
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>
                    <a href="{{ route('sales.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                       class="text-decoration-none text-dark sortable-header">
                        ID
                        @if(request('sort') == 'id')
                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                        @else
                            <i class="bi bi-arrow-down-up text-muted"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route('sales.index', array_merge(request()->query(), ['sort' => 'customer_name', 'direction' => request('sort') == 'customer_name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                       class="text-decoration-none text-dark sortable-header">
                        Customer
                        @if(request('sort') == 'customer_name')
                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                        @else
                            <i class="bi bi-arrow-down-up text-muted"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route('sales.index', array_merge(request()->query(), ['sort' => 'product_name', 'direction' => request('sort') == 'product_name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                       class="text-decoration-none text-dark sortable-header">
                        Product
                        @if(request('sort') == 'product_name')
                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                        @else
                            <i class="bi bi-arrow-down-up text-muted"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route('sales.index', array_merge(request()->query(), ['sort' => 'quantity', 'direction' => request('sort') == 'quantity' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                       class="text-decoration-none text-dark sortable-header">
                        Quantity
                        @if(request('sort') == 'quantity')
                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                        @else
                            <i class="bi bi-arrow-down-up text-muted"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route('sales.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                       class="text-decoration-none text-dark sortable-header">
                        Created At
                        @if(request('sort') == 'created_at')
                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                        @else
                            <i class="bi bi-arrow-down-up text-muted"></i>
                        @endif
                    </a>
                </th>
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
                                                    <td>{{ $sale->created_at->format('d-m-Y H:i') }}</td>
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
    
    <!-- Pagination -->
    @if($sales->hasPages())
    <div class="d-flex justify-content-center mt-4 p-3 bg-light rounded">
        {{ $sales->links() }}
    </div>
    @endif
</div>
@endsection