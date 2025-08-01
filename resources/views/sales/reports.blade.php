@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <h1 class="mb-0">Sales Report</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sales Report</li>
            </ol>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Advanced Report Filters</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAdvancedFilters()">
                            <i class="bi bi-funnel"></i> Toggle Advanced Filters
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="reportForm" method="GET" action="{{ route('sales.reports') }}">
                        <!-- Basic Filters -->
                        <div class="row">
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="text" class="form-control datepicker" id="date_from" name="date_from" 
                                       value="{{ request('date_from', date('d-m-Y', strtotime('-30 days'))) }}" 
                                       placeholder="dd-mm-yyyy">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="text" class="form-control datepicker" id="date_to" name="date_to" 
                                       value="{{ request('date_to', date('d-m-Y')) }}" 
                                       placeholder="dd-mm-yyyy">
                            </div>
                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    @foreach(\App\Models\Product::select('category')->distinct()->pluck('category') as $cat)
                                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                            {{ ucfirst($cat) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="region" class="form-label">Region</label>
                                <select class="form-select" id="region" name="region">
                                    <option value="">All Regions</option>
                                    @foreach(\App\Models\Customer::select('region')->distinct()->pluck('region') as $reg)
                                        <option value="{{ $reg }}" {{ request('region') == $reg ? 'selected' : '' }}>
                                            {{ ucfirst($reg) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Advanced Filters (Collapsible) -->
                        <div id="advancedFilters" class="row mt-3" style="display: none;">
                            <div class="col-md-3">
                                <label for="customer_id" class="form-label">Customer</label>
                                <select class="form-select" id="customer_id" name="customer_id">
                                    <option value="">All Customers</option>
                                    @foreach(\App\Models\Customer::select('id', 'name')->orderBy('name')->get() as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="product_id" class="form-label">Product</label>
                                <select class="form-select" id="product_id" name="product_id">
                                    <option value="">All Products</option>
                                    @foreach(\App\Models\Product::select('id', 'name')->orderBy('name')->get() as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="min_quantity" class="form-label">Min Quantity</label>
                                <input type="number" class="form-control" id="min_quantity" name="min_quantity" 
                                       value="{{ request('min_quantity') }}" min="1" placeholder="1">
                            </div>
                            <div class="col-md-3">
                                <label for="max_quantity" class="form-label">Max Quantity</label>
                                <input type="number" class="form-control" id="max_quantity" name="max_quantity" 
                                       value="{{ request('max_quantity') }}" min="1" placeholder="100">
                            </div>
                        </div>

                        <!-- More Advanced Filters -->
                        <div id="moreAdvancedFilters" class="row mt-3" style="display: none;">
                            <div class="col-md-3">
                                <label for="min_amount" class="form-label">Min Amount ($)</label>
                                <input type="number" class="form-control" id="min_amount" name="min_amount" 
                                       value="{{ request('min_amount') }}" min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label for="max_amount" class="form-label">Max Amount ($)</label>
                                <input type="number" class="form-control" id="max_amount" name="max_amount" 
                                       value="{{ request('max_amount') }}" min="0" step="0.01" placeholder="1000.00">
                            </div>
                            <div class="col-md-3">
                                <label for="gender" class="form-label">Customer Gender</label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value="">All Genders</option>
                                    <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="age_range" class="form-label">Age Range</label>
                                <select class="form-select" id="age_range" name="age_range">
                                    <option value="">All Ages</option>
                                    <option value="18-25" {{ request('age_range') == '18-25' ? 'selected' : '' }}>18-25</option>
                                    <option value="26-35" {{ request('age_range') == '26-35' ? 'selected' : '' }}>26-35</option>
                                    <option value="36-50" {{ request('age_range') == '36-50' ? 'selected' : '' }}>36-50</option>
                                    <option value="51+" {{ request('age_range') == '51+' ? 'selected' : '' }}>51+</option>
                                </select>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Generate Report
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="exportReport()">
                                    <i class="bi bi-download"></i> Export Data
                                </button>
                                <!-- <button type="button" class="btn btn-info" onclick="testExport()">
                                    <i class="bi bi-download"></i> Test Export
                                </button> -->
                                <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                                    <i class="bi bi-arrow-clockwise"></i> Reset Filters
                                </button>
                                <!-- <button type="button" class="btn btn-outline-primary" onclick="toggleMoreFilters()">
                                    <i class="bi bi-funnel-fill"></i> More Filters
                                </button> -->
                            </div>
                        </div>

                        <!-- Quick Filter Presets -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="applyQuickFilter('today')">
                                        <i class="bi bi-calendar-day"></i> Today
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="applyQuickFilter('this_week')">
                                        <i class="bi bi-calendar-week"></i> This Week
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="applyQuickFilter('this_month')">
                                        <i class="bi bi-calendar-month"></i> This Month
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="applyQuickFilter('high_value')">
                                        <i class="bi bi-currency-dollar"></i> High Value ($100+)
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="applyQuickFilter('bulk_orders')">
                                        <i class="bi bi-box-seam"></i> Bulk Orders (5+)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Summary -->
    @if(request('category') || request('region') || request('customer_id') || request('product_id') || request('min_quantity') || request('max_quantity') || request('min_amount') || request('max_amount') || request('gender') || request('age_range'))
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info">
                <h6 class="alert-heading"><i class="bi bi-funnel"></i> Active Filters:</h6>
                <div class="row">
                    @if(request('category'))
                        <div class="col-md-2">
                            <strong>Category:</strong> {{ ucfirst(request('category')) }}
                        </div>
                    @endif
                    @if(request('region'))
                        <div class="col-md-2">
                            <strong>Region:</strong> {{ ucfirst(request('region')) }}
                        </div>
                    @endif
                    @if(request('customer_id'))
                        <div class="col-md-2">
                            <strong>Customer:</strong> {{ \App\Models\Customer::find(request('customer_id'))->name ?? 'N/A' }}
                        </div>
                    @endif
                    @if(request('product_id'))
                        <div class="col-md-2">
                            <strong>Product:</strong> {{ \App\Models\Product::find(request('product_id'))->name ?? 'N/A' }}
                        </div>
                    @endif
                    @if(request('min_quantity') || request('max_quantity'))
                        <div class="col-md-2">
                            <strong>Quantity:</strong> 
                            @if(request('min_quantity') && request('max_quantity'))
                                {{ request('min_quantity') }} - {{ request('max_quantity') }}
                            @elseif(request('min_quantity'))
                                ≥ {{ request('min_quantity') }}
                            @else
                                ≤ {{ request('max_quantity') }}
                            @endif
                        </div>
                    @endif
                    @if(request('min_amount') || request('max_amount'))
                        <div class="col-md-2">
                            <strong>Amount:</strong> 
                            @if(request('min_amount') && request('max_amount'))
                                ${{ request('min_amount') }} - ${{ request('max_amount') }}
                            @elseif(request('min_amount'))
                                ≥ ${{ request('min_amount') }}
                            @else
                                ≤ ${{ request('max_amount') }}
                            @endif
                        </div>
                    @endif
                    @if(request('gender'))
                        <div class="col-md-2">
                            <strong>Gender:</strong> {{ ucfirst(request('gender')) }}
                        </div>
                    @endif
                    @if(request('age_range'))
                        <div class="col-md-2">
                            <strong>Age:</strong> {{ request('age_range') }}
                        </div>
                    @endif
                </div>
                <hr>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="resetFilters()">
                    <i class="bi bi-x-circle"></i> Clear All Filters
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-secondary mb-1">Total Revenue</p>
                            <h3 class="mb-0">${{ number_format($totalRevenue ?? 0, 0) }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-currency-dollar fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-secondary mb-1">Total Orders</p>
                            <h3 class="mb-0">{{ number_format($totalOrders ?? 0) }}</h3>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-cart-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-secondary mb-1">Average Order Value</p>
                            <h3 class="mb-0">${{ number_format($avgOrderValue ?? 0, 0) }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-graph-up fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-secondary mb-1">Conversion Rate</p>
                            <h3 class="mb-0">{{ number_format($conversionRate ?? 0, 1) }}%</h3>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-percent fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Charts -->
    <div class="row">
        <!-- Revenue Trend -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Revenue Trend Analysis</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setChartPeriod('7d')">7D</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setChartPeriod('30d')">30D</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary active" onclick="setChartPeriod('90d')">90D</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setChartPeriod('1y')">1Y</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="revenue-trend-chart"></div>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Top Selling Products</h3>
                </div>
                <div class="card-body">
                    <div id="top-products-chart"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Analytics -->
    <div class="row">
        <!-- Sales by Category -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Sales by Category</h3>
                </div>
                <div class="card-body">
                    <div id="category-sales-chart"></div>
                </div>
            </div>
        </div>

        <!-- Regional Performance -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Regional Performance</h3>
                </div>
                <div class="card-body">
                    <div id="regional-performance-chart"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Sales Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Detailed Sales Data</h3>
                        @if($salesData->total() > 0)
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary me-2">{{ $salesData->total() }}</span>
                            <span class="text-muted">sales records</span>
                            @if($salesData->hasPages())
                            <span class="text-muted ms-3">
                                Page {{ $salesData->currentPage() }} of {{ $salesData->lastPage() }}
                            </span>
                            @endif
                        </div>
                        @endif
                    </div>
                    <div class="card-tools mt-2">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="table_search" class="form-control float-end" placeholder="Search sales data...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark sortable-header">
                                        Order ID
                                        @if(request('sort') == 'id')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'customer', 'direction' => request('sort') == 'customer' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Customer
                                        @if(request('sort') == 'customer')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'product', 'direction' => request('sort') == 'product' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Product
                                        @if(request('sort') == 'product')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'category', 'direction' => request('sort') == 'category' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Category
                                        @if(request('sort') == 'category')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'region', 'direction' => request('sort') == 'region' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Region
                                        @if(request('sort') == 'region')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'quantity', 'direction' => request('sort') == 'quantity' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Quantity
                                        @if(request('sort') == 'quantity')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'amount', 'direction' => request('sort') == 'amount' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Amount
                                        @if(request('sort') == 'amount')
                                            <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'date', 'direction' => request('sort') == 'date' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Date
                                        @if(request('sort') == 'date')
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
                            @forelse($salesData ?? [] as $sale)
                            <tr>
                                <td>#{{ $sale->id }}</td>
                                <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                                <td>{{ $sale->product->name ?? 'N/A' }}</td>
                                <td>{{ $sale->product->category ?? 'N/A' }}</td>
                                <td>{{ $sale->customer->region ?? 'N/A' }}</td>
                                <td>{{ $sale->quantity }}</td>
                                <td>${{ number_format($sale->quantity * ($sale->product->price ?? 0), 2) }}</td>
                                <td>{{ $sale->created_at->format('d-m-Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No sales data available for the selected filters</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    @if($salesData->hasPages())
                    <div class="d-flex justify-content-center mt-4 p-3 bg-light rounded">
                        {{ $salesData->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
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
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
// Revenue Trend Chart
const revenueTrendOptions = {
    series: [
        {
            name: 'Revenue',
            data: [31000, 40000, 28000, 51000, 42000, 82000, 56000, 74000, 61000, 89000, 76000, 92000]
        },
        {
            name: 'Orders',
            data: [11000, 32000, 45000, 32000, 34000, 52000, 41000, 80000, 56000, 55000, 67000, 88000]
        }
    ],
    chart: {
        height: 350,
        type: 'area',
        toolbar: {
            show: false
        }
    },
    legend: {
        show: true,
        position: 'top'
    },
    colors: ['#0d6efd', '#20c997'],
    dataLabels: {
        enabled: false
    },
    stroke: {
        curve: 'smooth'
    },
    xaxis: {
        type: 'datetime',
        categories: [
            '2024-01-01', '2024-02-01', '2024-03-01', '2024-04-01',
            '2024-05-01', '2024-06-01', '2024-07-01', '2024-08-01',
            '2024-09-01', '2024-10-01', '2024-11-01', '2024-12-01'
        ]
    },
    tooltip: {
        x: {
            format: 'MMMM yyyy'
        }
    }
};

const revenueTrendChart = new ApexCharts(document.querySelector('#revenue-trend-chart'), revenueTrendOptions);
revenueTrendChart.render();

// Top Products Chart
const topProductsOptions = {
    series: [44, 55, 13, 43, 22],
    chart: {
        type: 'donut',
        height: 300
    },
    labels: ['iPhone 15 Pro', 'MacBook Pro', 'AirPods Pro', 'iPad Air', 'Apple Watch'],
    colors: ['#0d6efd', '#20c997', '#ffc107', '#dc3545', '#6f42c1'],
    legend: {
        position: 'bottom'
    }
};

const topProductsChart = new ApexCharts(document.querySelector('#top-products-chart'), topProductsOptions);
topProductsChart.render();

// Category Sales Chart
const categorySalesOptions = {
    series: [{
        name: 'Sales',
        data: [400, 430, 448, 470, 540, 580, 690, 1100, 1200, 1380]
    }],
    chart: {
        type: 'bar',
        height: 300
    },
    colors: ['#0d6efd'],
    plotOptions: {
        bar: {
            horizontal: true,
            dataLabels: {
                position: 'top'
            }
        }
    },
    dataLabels: {
        enabled: true,
        formatter: function (val) {
            return val + "K";
        },
        style: {
            fontSize: '12px',
            colors: ['#304758']
        }
    },
    xaxis: {
        categories: ['Electronics', 'Fashion', 'Home & Garden', 'Sports', 'Books', 'Beauty', 'Automotive', 'Toys', 'Health', 'Food']
    }
};

const categorySalesChart = new ApexCharts(document.querySelector('#category-sales-chart'), categorySalesOptions);
categorySalesChart.render();

// Regional Performance Chart
const regionalPerformanceOptions = {
    series: [
        {
            name: 'North America',
            data: [44, 55, 57, 56, 61, 58, 63, 60, 66]
        },
        {
            name: 'Europe',
            data: [76, 85, 101, 98, 87, 105, 91, 114, 94]
        },
        {
            name: 'Asia',
            data: [35, 41, 36, 26, 45, 48, 52, 53, 41]
        }
    ],
    chart: {
        type: 'bar',
        height: 300
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded'
        }
    },
    legend: {
        show: true,
        position: 'top'
    },
    colors: ['#0d6efd', '#20c997', '#ffc107'],
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
    },
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep']
    },
    fill: {
        opacity: 1
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return '$ ' + val + ' thousands';
            }
        }
    }
};

const regionalPerformanceChart = new ApexCharts(document.querySelector('#regional-performance-chart'), regionalPerformanceOptions);
regionalPerformanceChart.render();

// Export Report Function
function exportReport() {
    const form = document.getElementById('reportForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Create download link
    const downloadUrl = `/api/sales/export?${params.toString()}`;
    
    // Show loading indicator
    const exportBtn = event.target;
    const originalText = exportBtn.innerHTML;
    exportBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Exporting...';
    exportBtn.disabled = true;
    
    // Use fetch to check if the endpoint is accessible
    fetch(downloadUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.blob();
        })
        .then(blob => {
            // Create a blob URL
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `sales-report-${new Date().toISOString().split('T')[0]}.csv`;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            
            // Show success message
            alert('Export completed successfully!');
        })
        .catch(error => {
            console.error('Export failed:', error);
            alert('Failed to download report: ' + error.message);
        })
        .finally(() => {
            // Restore button
            exportBtn.innerHTML = originalText;
            exportBtn.disabled = false;
        });
}

function testExport() {
    // Test export without any filters
    const downloadUrl = `/api/sales/export`;
    
    // Show loading indicator
    const testBtn = event.target;
    const originalText = testBtn.innerHTML;
    testBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Testing...';
    testBtn.disabled = true;
    
    fetch(downloadUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.blob();
        })
        .then(blob => {
            // Create a blob URL
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `test-sales-report-${new Date().toISOString().split('T')[0]}.csv`;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            alert('Test export successful! File should download automatically.');
        })
        .catch(error => {
            console.error('Test export failed:', error);
            alert('Test export failed: ' + error.message);
        })
        .finally(() => {
            // Restore button
            testBtn.innerHTML = originalText;
            testBtn.disabled = false;
        });
}

function toggleAdvancedFilters() {
    const advancedFiltersDiv = document.getElementById('advancedFilters');
    const toggleBtn = event.target;

    if (advancedFiltersDiv.style.display === 'none') {
        advancedFiltersDiv.style.display = 'block';
        toggleBtn.innerHTML = '<i class="bi bi-funnel"></i> Hide Advanced Filters';
    } else {
        advancedFiltersDiv.style.display = 'none';
        toggleBtn.innerHTML = '<i class="bi bi-funnel"></i> Toggle Advanced Filters';
    }
}

function toggleMoreFilters() {
    const moreAdvancedFiltersDiv = document.getElementById('moreAdvancedFilters');
    const toggleBtn = event.target;

    if (moreAdvancedFiltersDiv.style.display === 'none') {
        moreAdvancedFiltersDiv.style.display = 'block';
        toggleBtn.innerHTML = '<i class="bi bi-funnel-fill"></i> Less Filters';
    } else {
        moreAdvancedFiltersDiv.style.display = 'none';
        toggleBtn.innerHTML = '<i class="bi bi-funnel-fill"></i> More Filters';
    }
}

function setChartPeriod(period) {
    // Highlight active button
    document.querySelectorAll('.card-header .btn-group .btn').forEach(btn => btn.classList.remove('active'));
    let activeBtn = Array.from(document.querySelectorAll('.card-header .btn-group .btn')).find(btn => btn.textContent ===
        (period === '7d' ? '7D' : period === '30d' ? '30D' : period === '90d' ? '90D' : '1Y'));
    if (activeBtn) activeBtn.classList.add('active');

    // Simulated data for each period
    let dataSets = {
        '7d': {
            categories: [
                '2025-07-25', '2025-07-26', '2025-07-27', '2025-07-28', '2025-07-29', '2025-07-30', '2025-07-31'
            ],
            revenue: [12000, 15000, 11000, 17000, 16000, 18000, 21000],
            orders: [5000, 7000, 6000, 8000, 7500, 9000, 9500]
        },
        '30d': {
            categories: Array.from({length: 30}, (_, i) => {
                let d = new Date('2025-07-02');
                d.setDate(d.getDate() + i);
                return d.toISOString().slice(0, 10);
            }),
            revenue: Array.from({length: 30}, () => Math.floor(10000 + Math.random() * 15000)),
            orders: Array.from({length: 30}, () => Math.floor(4000 + Math.random() * 8000))
        },
        '90d': {
            categories: Array.from({length: 13}, (_, i) => {
                let d = new Date('2025-05-01');
                d.setDate(d.getDate() + i * 7);
                return d.toISOString().slice(0, 10);
            }),
            revenue: [31000, 40000, 28000, 51000, 42000, 82000, 56000, 74000, 61000, 89000, 76000, 92000, 98000],
            orders: [11000, 32000, 45000, 32000, 34000, 52000, 41000, 80000, 56000, 55000, 67000, 88000, 99000]
        },
        '1y': {
            categories: [
                '2024-08-01', '2024-09-01', '2024-10-01', '2024-11-01',
                '2024-12-01', '2025-01-01', '2025-02-01', '2025-03-01',
                '2025-04-01', '2025-05-01', '2025-06-01', '2025-07-01'
            ],
            revenue: [31000, 40000, 28000, 51000, 42000, 82000, 56000, 74000, 61000, 89000, 76000, 92000],
            orders: [11000, 32000, 45000, 32000, 34000, 52000, 41000, 80000, 56000, 55000, 67000, 88000]
        }
    };
    let data = dataSets[period] || dataSets['90d'];
    revenueTrendChart.updateOptions({
        xaxis: { categories: data.categories },
        series: [
            { name: 'Revenue', data: data.revenue },
            { name: 'Orders', data: data.orders }
        ]
    });
}

// Initialize datepickers
$(document).ready(function() {
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true,
        orientation: 'bottom auto',
        clearBtn: true
    });
});

// Auto-submit form when certain filters change
document.addEventListener('DOMContentLoaded', function() {
    const autoSubmitFilters = ['customer_id', 'product_id', 'category', 'region', 'gender', 'age_range'];
    
    autoSubmitFilters.forEach(filterId => {
        const element = document.getElementById(filterId);
        if (element) {
            element.addEventListener('change', function() {
                // Add a small delay to allow multiple selections
                setTimeout(() => {
                    document.getElementById('reportForm').submit();
                }, 500);
            });
        }
    });

    // Show active filters count
    updateActiveFiltersCount();
});

function updateActiveFiltersCount() {
    const form = document.getElementById('reportForm');
    const formData = new FormData(form);
    let activeFilters = 0;
    
    for (let [key, value] of formData.entries()) {
        if (value && key !== 'date_from' && key !== 'date_to') {
            activeFilters++;
        }
    }
    
    // Update the filter button text to show count
    const filterBtn = document.querySelector('[onclick="toggleAdvancedFilters()"]');
    if (filterBtn && activeFilters > 0) {
        filterBtn.innerHTML = `<i class="bi bi-funnel"></i> Filters (${activeFilters})`;
    }
}

// Reset filters function
function resetFilters() {
    const form = document.getElementById('reportForm');
    
    // Reset date fields to last 30 days in dd-mm-yyyy format
    const today = new Date();
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(today.getDate() - 30);
    
    // Format dates as dd-mm-yyyy
    const formatDate = (date) => {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    };
    
    form.date_from.value = formatDate(thirtyDaysAgo);
    form.date_to.value = formatDate(today);
    
    // Reset all select fields to empty
    const selectFields = ['category', 'region', 'customer_id', 'product_id', 'gender', 'age_range'];
    selectFields.forEach(field => {
        const element = form.querySelector(`[name="${field}"]`);
        if (element) {
            element.value = '';
        }
    });
    
    // Reset all number input fields
    const numberFields = ['min_quantity', 'max_quantity', 'min_amount', 'max_amount'];
    numberFields.forEach(field => {
        const element = form.querySelector(`[name="${field}"]`);
        if (element) {
            element.value = '';
        }
    });
    
    // Hide advanced filters
    document.getElementById('advancedFilters').style.display = 'none';
    document.getElementById('moreAdvancedFilters').style.display = 'none';
    
    // Update the toggle button text
    const toggleBtn = document.querySelector('[onclick="toggleAdvancedFilters()"]');
    if (toggleBtn) {
        toggleBtn.innerHTML = '<i class="bi bi-funnel"></i> Toggle Advanced Filters';
    }
    
    // Submit the form
    form.submit();
}

// Quick filter presets
function applyQuickFilter(preset) {
    const form = document.getElementById('reportForm');
    
    // Helper function to format date as dd-mm-yyyy
    const formatDate = (date) => {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    };
    
    switch(preset) {
        case 'today':
            const today = new Date();
            form.date_from.value = formatDate(today);
            form.date_to.value = formatDate(today);
            break;
        case 'this_week':
            const weekToday = new Date();
            const startOfWeek = new Date(weekToday.setDate(weekToday.getDate() - weekToday.getDay()));
            const endOfWeek = new Date(weekToday.setDate(weekToday.getDate() - weekToday.getDay() + 6));
            form.date_from.value = formatDate(startOfWeek);
            form.date_to.value = formatDate(endOfWeek);
            break;
        case 'this_month':
            const now = new Date();
            form.date_from.value = formatDate(new Date(now.getFullYear(), now.getMonth(), 1));
            form.date_to.value = formatDate(new Date(now.getFullYear(), now.getMonth() + 1, 0));
            break;
        case 'high_value':
            form.min_amount.value = '100';
            break;
        case 'bulk_orders':
            form.min_quantity.value = '5';
            break;
    }
    
    form.submit();
}
</script>
@endpush
@endsection