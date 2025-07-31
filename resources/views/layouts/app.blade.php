<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/sales/reports">Sales Report</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link{{ request()->is('sales/reports*') ? ' active' : '' }}" href="/sales/reports">
                        <i class="bi bi-graph-up"></i> Sales Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{{ request()->is('customers*') ? ' active' : '' }}" href="/customers">
                        <i class="bi bi-people"></i> Customers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{{ request()->is('products*') ? ' active' : '' }}" href="/products">
                        <i class="bi bi-box"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{{ request()->is('sales*') ? ' active' : '' }}" href="/sales">
                        <i class="bi bi-cart"></i> Sales
                    </a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link{{ request()->is('sales/reports*') ? ' active' : '' }}" href="/sales/reports">
                        <i class="bi bi-graph-up"></i> Sales Reports
                    </a>
                </li> -->
            </ul>
        </div>
    </div>
</nav>
@yield('content')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html> 