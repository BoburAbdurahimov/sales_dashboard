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
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-sm-6">
            <h1 class="mb-0">Customers</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Customers</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer Management</h3>
                    <div class="card-tools">
                        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus"></i> Add Customer
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <!-- Search Section -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <form method="GET" action="{{ route('customers.index') }}" class="input-group">
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by name, email, phone, region...">
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="bi bi-search"></i>
                                </button>
                                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x"></i>
                                </a>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-funnel"></i> Filters
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#filterModal">Advanced Filters</a></li>
                                    <li><a class="dropdown-item" href="{{ route('customers.index') }}">Clear All</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Results Summary -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            @if($customers->total() > 0)
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info me-2">{{ $customers->total() }}</span>
                                <span class="text-muted">customers found</span>
                                @if($customers->hasPages())
                                <span class="text-muted ms-3">
                                    Page {{ $customers->currentPage() }} of {{ $customers->lastPage() }}
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

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="{{ route('customers.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
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
                                        <a href="{{ route('customers.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                           class="text-decoration-none text-dark sortable-header">
                                            Name
                                            @if(request('sort') == 'name')
                                                <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="bi bi-arrow-down-up text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('customers.index', array_merge(request()->query(), ['sort' => 'email', 'direction' => request('sort') == 'email' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                           class="text-decoration-none text-dark sortable-header">
                                            Email
                                            @if(request('sort') == 'email')
                                                <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="bi bi-arrow-down-up text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('customers.index', array_merge(request()->query(), ['sort' => 'phone_number', 'direction' => request('sort') == 'phone_number' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                           class="text-decoration-none text-dark sortable-header">
                                            Phone
                                            @if(request('sort') == 'phone_number')
                                                <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="bi bi-arrow-down-up text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('customers.index', array_merge(request()->query(), ['sort' => 'region', 'direction' => request('sort') == 'region' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                           class="text-decoration-none text-dark sortable-header">
                                            Region
                                            @if(request('sort') == 'region')
                                                <i class="bi bi-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="bi bi-arrow-down-up text-muted"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('customers.index', array_merge(request()->query(), ['sort' => 'gender', 'direction' => request('sort') == 'gender' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                           class="text-decoration-none text-dark sortable-header">
                                            Gender
                                            @if(request('sort') == 'gender')
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
                                @forelse($customers as $customer)
                                    <tr>
                                        <td>{{ $customer->id }}</td>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->phone_number }}</td>
                                        <td><span class="badge bg-info">{{ $customer->region }}</span></td>
                                        <td>
                                            @if($customer->gender === 'male')
                                                <span class="badge bg-primary">{{ ucfirst($customer->gender) }}</span>
                                            @elseif($customer->gender === 'female')
                                                <span class="badge bg-pink">{{ ucfirst($customer->gender) }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($customer->gender) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this customer?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No customers found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <!-- Pagination -->
                        @if($customers->hasPages())
                        <div class="d-flex justify-content-center mt-4 p-3 bg-light rounded">
                            {{ $customers->links() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Advanced Filters</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('customers.index') }}">
                <div class="modal-body">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                    <div class="mb-3">
                        <label for="region" class="form-label">Filter by Region</label>
                        <select class="form-select" id="region" name="region">
                            <option value="">All Regions</option>
                            @foreach($regions as $region)
                                <option value="{{ $region }}" {{ request('region') == $region ? 'selected' : '' }}>{{ $region }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="gender" class="form-label">Filter by Gender</label>
                        <select class="form-select" id="gender" name="gender">
                            <option value="">All Genders</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender }}" {{ request('gender') == $gender ? 'selected' : '' }}>{{ ucfirst($gender) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 