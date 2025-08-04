<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Sales by product category
        $salesByCategory = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->select('products.category', DB::raw('SUM(sales.quantity) as total_quantity'))
            ->groupBy('products.category')
            ->pluck('total_quantity', 'products.category');

        // Sales by region
        $salesByRegion = Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->select('customers.region', DB::raw('SUM(sales.quantity) as total_quantity'))
            ->groupBy('customers.region')
            ->pluck('total_quantity', 'customers.region');

        // Sales over time (by month) - compatible with SQLite and MySQL
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $dateFormat = "strftime('%Y-%m', created_at)";
        } else {
            $dateFormat = "DATE_FORMAT(created_at, '%Y-%m')";
        }
        $salesOverTime = Sale::select(DB::raw("$dateFormat as month"), DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_quantity', 'month');

        // Recent sales without pagination
        $recentSales = Sale::with(['customer', 'product'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate additional statistics - using product price instead of unit_price
        $totalSales = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->sum(DB::raw('sales.quantity * products.price'));
        $totalOrders = Sale::count();
        $totalCustomers = Customer::count();
        $totalProducts = Product::count();

        // Get paginated sales data for the reports view
        $salesData = Sale::with(['customer', 'product'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculate additional metrics for consistency with reports method
        $totalRevenue = $totalSales; // Use the same value as totalSales
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $conversionRate = 3.2; // Default conversion rate

        // Generate chart data for dashboard
        $chartData = $this->generateChartData(now()->subDays(30), now(), null, null, null, null, null, null, null, null, null, null);

        return view('sales.reports', [
            'salesData' => $salesData,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'avgOrderValue' => $avgOrderValue,
            'conversionRate' => $conversionRate,
            'chartData' => $chartData,
            'salesByCategory' => $salesByCategory,
            'salesByRegion' => $salesByRegion,
            'salesOverTime' => $salesOverTime,
            'recentSales' => $recentSales,
            'totalSales' => $totalSales,
            'totalCustomers' => $totalCustomers,
            'totalProducts' => $totalProducts,
            'filters' => [
                'date_from' => now()->subDays(30)->format('d-m-Y'),
                'date_to' => now()->format('d-m-Y'),
                'category' => null,
                'region' => null,
                'customer_id' => null,
                'product_id' => null,
                'min_quantity' => null,
                'max_quantity' => null,
                'min_amount' => null,
                'max_amount' => null,
                'gender' => null,
                'age_range' => null,
            ],
        ]);
    }

    public function reports(Request $request)
    {
        // Get filter parameters and convert date format from dd-mm-yyyy to Y-m-d
        $dateFromInput = $request->get('date_from');
        $dateToInput = $request->get('date_to');
        
        // Convert dd-mm-yyyy to Y-m-d format for database queries
        $dateFrom = $dateFromInput ? \Carbon\Carbon::createFromFormat('d-m-Y', $dateFromInput)->startOfDay() : now()->subDays(30);
        $dateTo = $dateToInput ? \Carbon\Carbon::createFromFormat('d-m-Y', $dateToInput)->endOfDay() : now();
        $category = $request->get('category');
        $region = $request->get('region');
        $customerId = $request->get('customer_id');
        $productId = $request->get('product_id');
        $minQuantity = $request->get('min_quantity');
        $maxQuantity = $request->get('max_quantity');
        $minAmount = $request->get('min_amount');
        $maxAmount = $request->get('max_amount');
        $gender = $request->get('gender');
        $ageRange = $request->get('age_range');

        // Build query for sales data
        $salesQuery = Sale::with(['customer', 'product'])
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo]);

        // Apply category filter
        if ($category) {
            $salesQuery->whereHas('product', function ($query) use ($category) {
                $query->where('category', $category);
            });
        }

        // Apply region filter
        if ($region) {
            $salesQuery->whereHas('customer', function ($query) use ($region) {
                $query->where('region', $region);
            });
        }

        // Apply customer filter
        if ($customerId) {
            $salesQuery->where('customer_id', $customerId);
        }

        // Apply product filter
        if ($productId) {
            $salesQuery->where('product_id', $productId);
        }

        // Apply quantity range filters
        if ($minQuantity) {
            $salesQuery->where('sales.quantity', '>=', $minQuantity);
        }
        if ($maxQuantity) {
            $salesQuery->where('sales.quantity', '<=', $maxQuantity);
        }

        // Apply gender filter
        if ($gender) {
            $salesQuery->whereHas('customer', function ($query) use ($gender) {
                $query->where('gender', $gender);
            });
        }

        // Apply age range filter
        if ($ageRange) {
            $salesQuery->whereHas('customer', function ($query) use ($ageRange) {
                switch ($ageRange) {
                    case '18-25':
                        $query->whereBetween('age', [18, 25]);
                        break;
                    case '26-35':
                        $query->whereBetween('age', [26, 35]);
                        break;
                    case '36-50':
                        $query->whereBetween('age', [36, 50]);
                        break;
                    case '51+':
                        $query->where('age', '>=', 51);
                        break;
                }
            });
        }

        // Apply sorting to the query
        $sort = $request->get('sort', 'date');
        $direction = $request->get('direction', 'desc');
        
        // Apply sorting to the query based on the requested column
        switch ($sort) {
            case 'id':
                $salesQuery->orderBy('id', $direction);
                break;
            case 'customer':
                $salesQuery->join('customers', 'sales.customer_id', '=', 'customers.id')
                          ->orderBy('customers.name', $direction);
                break;
            case 'product':
                $salesQuery->join('products', 'sales.product_id', '=', 'products.id')
                          ->orderBy('products.name', $direction);
                break;
            case 'category':
                $salesQuery->join('products', 'sales.product_id', '=', 'products.id')
                          ->orderBy('products.category', $direction);
                break;
            case 'region':
                $salesQuery->join('customers', 'sales.customer_id', '=', 'customers.id')
                          ->orderBy('customers.region', $direction);
                break;
            case 'quantity':
                $salesQuery->orderBy('quantity', $direction);
                break;
            case 'amount':
                // For amount sorting, we'll need to calculate it in the query
                $salesQuery->join('products', 'sales.product_id', '=', 'products.id')
                          ->orderByRaw('(sales.quantity * products.price)', $direction);
                break;
            case 'date':
            default:
                $salesQuery->orderBy('created_at', $direction);
                break;
        }
        
        // Apply amount filters to the query
        if ($minAmount || $maxAmount) {
            $salesQuery->join('products', 'sales.product_id', '=', 'products.id');
            if ($minAmount) {
                $salesQuery->whereRaw('(sales.quantity * products.price) >= ?', [$minAmount]);
            }
            if ($maxAmount) {
                $salesQuery->whereRaw('(sales.quantity * products.price) <= ?', [$maxAmount]);
            }
        }
        
        // Get paginated results
        $salesData = $salesQuery->paginate(15)->withQueryString();

        // Calculate report statistics - using product price instead of unit_price
        $totalRevenueQuery = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo]);
        
        // Apply the same filters to revenue calculation
        if ($category) {
            $totalRevenueQuery->where('products.category', $category);
        }
        if ($region) {
            $totalRevenueQuery->join('customers', 'sales.customer_id', '=', 'customers.id')
                            ->where('customers.region', $region);
        }
        if ($customerId) {
            $totalRevenueQuery->where('sales.customer_id', $customerId);
        }
        if ($productId) {
            $totalRevenueQuery->where('sales.product_id', $productId);
        }
        if ($minQuantity) {
            $totalRevenueQuery->where('sales.quantity', '>=', $minQuantity);
        }
        if ($maxQuantity) {
            $totalRevenueQuery->where('sales.quantity', '<=', $maxQuantity);
        }
        if ($minAmount) {
            $totalRevenueQuery->whereRaw('(sales.quantity * products.price) >= ?', [$minAmount]);
        }
        if ($maxAmount) {
            $totalRevenueQuery->whereRaw('(sales.quantity * products.price) <= ?', [$maxAmount]);
        }
        if ($gender) {
            if (!$region) {
                $totalRevenueQuery->join('customers', 'sales.customer_id', '=', 'customers.id');
            }
            $totalRevenueQuery->where('customers.gender', $gender);
        }
        if ($ageRange) {
            if (!$region && !$gender) {
                $totalRevenueQuery->join('customers', 'sales.customer_id', '=', 'customers.id');
            }
            switch ($ageRange) {
                case '18-25':
                    $totalRevenueQuery->whereBetween('customers.age', [18, 25]);
                    break;
                case '26-35':
                    $totalRevenueQuery->whereBetween('customers.age', [26, 35]);
                    break;
                case '36-50':
                    $totalRevenueQuery->whereBetween('customers.age', [36, 50]);
                    break;
                case '51+':
                    $totalRevenueQuery->where('customers.age', '>=', 51);
                    break;
            }
        }
        
        $totalRevenue = $totalRevenueQuery->sum(DB::raw('sales.quantity * products.price'));
        
        // Calculate total orders with the same filters
        $totalOrdersQuery = Sale::whereBetween('sales.created_at', [$dateFrom, $dateTo]);
        
        if ($category) {
            $totalOrdersQuery->whereHas('product', function ($query) use ($category) {
                $query->where('category', $category);
            });
        }
        if ($region) {
            $totalOrdersQuery->whereHas('customer', function ($query) use ($region) {
                $query->where('region', $region);
            });
        }
        if ($customerId) {
            $totalOrdersQuery->where('sales.customer_id', $customerId);
        }
        if ($productId) {
            $totalOrdersQuery->where('sales.product_id', $productId);
        }
        if ($minQuantity) {
            $totalOrdersQuery->where('sales.quantity', '>=', $minQuantity);
        }
        if ($maxQuantity) {
            $totalOrdersQuery->where('sales.quantity', '<=', $maxQuantity);
        }
        if ($minAmount || $maxAmount) {
            $totalOrdersQuery->join('products', 'sales.product_id', '=', 'products.id');
            if ($minAmount) {
                $totalOrdersQuery->whereRaw('(sales.quantity * products.price) >= ?', [$minAmount]);
            }
            if ($maxAmount) {
                $totalOrdersQuery->whereRaw('(sales.quantity * products.price) <= ?', [$maxAmount]);
            }
        }
        if ($gender) {
            $totalOrdersQuery->whereHas('customer', function ($query) use ($gender) {
                $query->where('gender', $gender);
            });
        }
        if ($ageRange) {
            $totalOrdersQuery->whereHas('customer', function ($query) use ($ageRange) {
                switch ($ageRange) {
                    case '18-25':
                        $query->whereBetween('age', [18, 25]);
                        break;
                    case '26-35':
                        $query->whereBetween('age', [26, 35]);
                        break;
                    case '36-50':
                        $query->whereBetween('age', [36, 50]);
                        break;
                    case '51+':
                        $query->where('age', '>=', 51);
                        break;
                }
            });
        }
        
        $totalOrders = $totalOrdersQuery->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $conversionRate = 3.2; // This would be calculated based on your business logic

        // Generate chart data
        $chartData = $this->generateChartData($dateFrom, $dateTo, $category, $region, $customerId, $productId, $minQuantity, $maxQuantity, $minAmount, $maxAmount, $gender, $ageRange);

        return view('sales.reports', [
            'salesData' => $salesData,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'avgOrderValue' => $avgOrderValue,
            'conversionRate' => $conversionRate,
            'chartData' => $chartData,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'category' => $category,
                'region' => $region,
                'customer_id' => $customerId,
                'product_id' => $productId,
                'min_quantity' => $minQuantity,
                'max_quantity' => $maxQuantity,
                'min_amount' => $minAmount,
                'max_amount' => $maxAmount,
                'gender' => $gender,
                'age_range' => $ageRange,
            ],
        ]);
    }

    private function generateChartData($dateFrom, $dateTo, $category, $region, $customerId, $productId, $minQuantity, $maxQuantity, $minAmount, $maxAmount, $gender, $ageRange)
    {
        $chartData = [
            'revenueTrend' => [],
            'topProducts' => [],
            'salesByCategory' => [],
            'regionalPerformance' => [],
        ];

        // Revenue Trend (Monthly)
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $dateFormat = "strftime('%Y-%m', sales.created_at)";
        } else {
            $dateFormat = "DATE_FORMAT(sales.created_at, '%Y-%m')";
        }
        $revenueTrend = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->select(DB::raw("$dateFormat as month"), DB::raw('SUM(sales.quantity * products.price) as total_revenue'))
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            });
        $chartData['revenueTrend'] = $revenueTrend;

        // Top Products
        $topProducts = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo])
            ->select('sales.product_id', 'products.name as product_name', DB::raw('SUM(sales.quantity) as total_quantity, SUM(sales.quantity * products.price) as total_revenue'))
            ->groupBy('sales.product_id', 'products.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'product_name' => $item->product_name,
                    'total_quantity' => (int) $item->total_quantity,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            });
        $chartData['topProducts'] = $topProducts;

        // Sales by Category
        $salesByCategory = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo])
            ->select('products.category', DB::raw('SUM(sales.quantity) as total_quantity, SUM(sales.quantity * products.price) as total_revenue'))
            ->groupBy('products.category')
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'total_quantity' => (int) $item->total_quantity,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            });
        $chartData['salesByCategory'] = $salesByCategory;

        // Regional Performance
        $regionalPerformance = Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo])
            ->select('customers.region', DB::raw('SUM(sales.quantity) as total_quantity, SUM(sales.quantity * products.price) as total_revenue'))
            ->groupBy('customers.region')
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'region' => $item->region,
                    'total_quantity' => (int) $item->total_quantity,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            });
        $chartData['regionalPerformance'] = $regionalPerformance;

        return $chartData;
    }
} 