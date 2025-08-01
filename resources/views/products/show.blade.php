@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Product Details</h1>
        <div>
            <a href="{{ route('sales.create') }}?product_id={{ $product->id }}" class="btn btn-success">Add to Sale</a>
            <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ $product->name }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Product Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $product->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $product->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td><span class="badge bg-info">{{ $product->category }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Price:</strong></td>
                                    <td><span class="h5 text-success">${{ number_format($product->price, 2) }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Stock Level:</strong></td>
                                    <td>
                                        <span class="badge {{ $product->quantity > 10 ? 'bg-success' : ($product->quantity > 0 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $product->quantity }} {{ $product->quantity == 1 ? 'item' : 'items' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $product->created_at->format('d-m-Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $product->updated_at->format('d-m-Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Stock Status</h6>
                            <div class="alert {{ $product->quantity > 10 ? 'alert-success' : ($product->quantity > 0 ? 'alert-warning' : 'alert-danger') }}">
                                @if($product->quantity > 10)
                                    <i class="fas fa-check-circle"></i> <strong>In Stock</strong><br>
                                    <small>Plenty of inventory available</small>
                                @elseif($product->quantity > 0)
                                    <i class="fas fa-exclamation-triangle"></i> <strong>Low Stock</strong><br>
                                    <small>Only {{ $product->quantity }} items remaining</small>
                                @else
                                    <i class="fas fa-times-circle"></i> <strong>Out of Stock</strong><br>
                                    <small>No inventory available</small>
                                @endif
                            </div>
                            
                            <h6 class="text-muted">Quick Actions</h6>
                            <div class="d-grid gap-2">
                                @if($product->quantity > 0)
                                    <a href="{{ route('sales.create') }}?product_id={{ $product->id }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-shopping-cart"></i> Create Sale
                                    </a>
                                @else
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        <i class="fas fa-shopping-cart"></i> Out of Stock
                                    </button>
                                @endif
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit Product
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Product Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="h2 text-primary">${{ number_format($product->price, 2) }}</div>
                        <small class="text-muted">Unit Price</small>
                    </div>
                    
                    <div class="text-center mb-3">
                        <div class="h3 {{ $product->quantity > 10 ? 'text-success' : ($product->quantity > 0 ? 'text-warning' : 'text-danger') }}">
                            {{ $product->quantity }}
                        </div>
                        <small class="text-muted">Available Units</small>
                    </div>
                    
                    <div class="text-center">
                        <div class="h4 text-info">${{ number_format($product->price * $product->quantity, 2) }}</div>
                        <small class="text-muted">Total Inventory Value</small>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($product->quantity > 0)
                            <a href="{{ route('sales.create') }}?product_id={{ $product->id }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add to Sale
                            </a>
                        @endif
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Product
                        </a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-grid">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">
                                <i class="fas fa-trash"></i> Delete Product
                            </button>
                        </form>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 