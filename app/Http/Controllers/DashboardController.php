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
        $conversionRate = $this->calculateConversionRate(now()->subDays(30), now(), null, null, null, null, null, null, null, null, null, null);

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
        $conversionRate = $this->calculateConversionRate($dateFrom, $dateTo, $category, $region, $customerId, $productId, $minQuantity, $maxQuantity, $minAmount, $maxAmount, $gender, $ageRange);

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

    public function calculateConversionRate($dateFrom, $dateTo, $category, $region, $customerId, $productId, $minQuantity, $maxQuantity, $minAmount, $maxAmount, $gender, $ageRange)
    {
        // Calculate conversion rate based on multiple factors
        
        // 1. Customer Purchase Rate (customers who made purchases in the period)
        $customersWithPurchases = Sale::whereBetween('created_at', [$dateFrom, $dateTo])
            ->distinct('customer_id')
            ->count('customer_id');
        
        $totalCustomers = Customer::count();
        $customerConversionRate = $totalCustomers > 0 ? ($customersWithPurchases / $totalCustomers) * 100 : 0;
        
        // 2. Repeat Purchase Rate (customers with multiple orders)
        $repeatCustomers = Sale::whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        
        $repeatPurchaseRate = $customersWithPurchases > 0 ? ($repeatCustomers / $customersWithPurchases) * 100 : 0;
        
        // 3. Average Order Value Growth Rate
        $currentPeriodAvg = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo])
            ->avg(DB::raw('sales.quantity * products.price'));
        
        $previousPeriodStart = $dateFrom->copy()->subDays($dateFrom->diffInDays($dateTo));
        $previousPeriodEnd = $dateFrom->copy()->subDays(1);
        
        $previousPeriodAvg = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->avg(DB::raw('sales.quantity * products.price'));
        
        $avgOrderGrowthRate = $previousPeriodAvg > 0 ? (($currentPeriodAvg - $previousPeriodAvg) / $previousPeriodAvg) * 100 : 0;
        
        // 4. Product Category Performance
        $categoryPerformance = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo])
            ->groupBy('products.category')
            ->select('products.category', DB::raw('COUNT(*) as order_count'))
            ->orderBy('order_count', 'desc')
            ->first();
        
        $topCategoryRate = $categoryPerformance ? 100 : 0; // If we have top performing category
        
        // 5. Regional Performance
        $regionalPerformance = Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo])
            ->groupBy('customers.region')
            ->select('customers.region', DB::raw('COUNT(*) as order_count'))
            ->orderBy('order_count', 'desc')
            ->first();
        
        $topRegionRate = $regionalPerformance ? 100 : 0; // If we have top performing region
        
        // Calculate weighted conversion rate
        $conversionRate = (
            ($customerConversionRate * 0.3) +      // 30% weight
            ($repeatPurchaseRate * 0.25) +         // 25% weight
            (max(0, $avgOrderGrowthRate) * 0.2) + // 20% weight (only positive growth)
            ($topCategoryRate * 0.15) +            // 15% weight
            ($topRegionRate * 0.1)                 // 10% weight
        );
        
        // Ensure the rate is between 0 and 100
        $conversionRate = max(0, min(100, $conversionRate));
        
        return round($conversionRate, 1);
    }

    private function generateChartData($dateFrom, $dateTo, $category, $region, $customerId, $productId, $minQuantity, $maxQuantity, $minAmount, $maxAmount, $gender, $ageRange)
    {
        $chartData = [
            'revenueTrend' => [],
            'topProducts' => [],
            'salesByCategory' => [],
            'regionalPerformance' => [],
        ];

        // Calculate the date range difference to determine grouping
        $daysDiff = $dateFrom->diffInDays($dateTo);
        
        // Determine date format based on period length
        $driver = DB::getDriverName();
        if ($daysDiff <= 30) {
            // For 30 days or less, group by day
            if ($driver === 'sqlite') {
                $dateFormat = "strftime('%Y-%m-%d', sales.created_at)";
            } else {
                $dateFormat = "DATE_FORMAT(sales.created_at, '%Y-%m-%d')";
            }
            $dateColumn = 'day';
        } elseif ($daysDiff <= 90) {
            // For 90 days or less, group by week
            if ($driver === 'sqlite') {
                $dateFormat = "strftime('%Y-W%W', sales.created_at)";
            } else {
                $dateFormat = "DATE_FORMAT(sales.created_at, '%Y-W%u')";
            }
            $dateColumn = 'week';
        } else {
            // For longer periods, group by month
            if ($driver === 'sqlite') {
                $dateFormat = "strftime('%Y-%m', sales.created_at)";
            } else {
                $dateFormat = "DATE_FORMAT(sales.created_at, '%Y-%m')";
            }
            $dateColumn = 'month';
        }

        // Build the base query with filters
        $revenueTrendQuery = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo]);

        // Apply filters
        if ($category) {
            $revenueTrendQuery->where('products.category', $category);
        }
        if ($region) {
            $revenueTrendQuery->join('customers', 'sales.customer_id', '=', 'customers.id')
                            ->where('customers.region', $region);
        }
        if ($customerId) {
            $revenueTrendQuery->where('sales.customer_id', $customerId);
        }
        if ($productId) {
            $revenueTrendQuery->where('sales.product_id', $productId);
        }
        if ($minQuantity) {
            $revenueTrendQuery->where('sales.quantity', '>=', $minQuantity);
        }
        if ($maxQuantity) {
            $revenueTrendQuery->where('sales.quantity', '<=', $maxQuantity);
        }
        if ($minAmount) {
            $revenueTrendQuery->whereRaw('(sales.quantity * products.price) >= ?', [$minAmount]);
        }
        if ($maxAmount) {
            $revenueTrendQuery->whereRaw('(sales.quantity * products.price) <= ?', [$maxAmount]);
        }
        if ($gender) {
            if (!$region) {
                $revenueTrendQuery->join('customers', 'sales.customer_id', '=', 'customers.id');
            }
            $revenueTrendQuery->where('customers.gender', $gender);
        }
        if ($ageRange) {
            if (!$region && !$gender) {
                $revenueTrendQuery->join('customers', 'sales.customer_id', '=', 'customers.id');
            }
            switch ($ageRange) {
                case '18-25':
                    $revenueTrendQuery->whereBetween('customers.age', [18, 25]);
                    break;
                case '26-35':
                    $revenueTrendQuery->whereBetween('customers.age', [26, 35]);
                    break;
                case '36-50':
                    $revenueTrendQuery->whereBetween('customers.age', [36, 50]);
                    break;
                case '51+':
                    $revenueTrendQuery->where('customers.age', '>=', 51);
                    break;
            }
        }

        // Revenue Trend
        $revenueTrend = $revenueTrendQuery
            ->select(DB::raw("$dateFormat as $dateColumn"), DB::raw('SUM(sales.quantity * products.price) as total_revenue'))
            ->groupBy($dateColumn)
            ->orderBy($dateColumn)
            ->get()
            ->map(function ($item) use ($dateColumn) {
                return [
                    'month' => $item->$dateColumn,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            });
        $chartData['revenueTrend'] = $revenueTrend;

        // Top Products
        $topProductsQuery = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo]);

        // Apply the same filters to top products query
        if ($category) {
            $topProductsQuery->where('products.category', $category);
        }
        if ($region) {
            $topProductsQuery->join('customers', 'sales.customer_id', '=', 'customers.id')
                            ->where('customers.region', $region);
        }
        if ($customerId) {
            $topProductsQuery->where('sales.customer_id', $customerId);
        }
        if ($productId) {
            $topProductsQuery->where('sales.product_id', $productId);
        }
        if ($minQuantity) {
            $topProductsQuery->where('sales.quantity', '>=', $minQuantity);
        }
        if ($maxQuantity) {
            $topProductsQuery->where('sales.quantity', '<=', $maxQuantity);
        }
        if ($minAmount) {
            $topProductsQuery->whereRaw('(sales.quantity * products.price) >= ?', [$minAmount]);
        }
        if ($maxAmount) {
            $topProductsQuery->whereRaw('(sales.quantity * products.price) <= ?', [$maxAmount]);
        }
        if ($gender) {
            if (!$region) {
                $topProductsQuery->join('customers', 'sales.customer_id', '=', 'customers.id');
            }
            $topProductsQuery->where('customers.gender', $gender);
        }
        if ($ageRange) {
            if (!$region && !$gender) {
                $topProductsQuery->join('customers', 'sales.customer_id', '=', 'customers.id');
            }
            switch ($ageRange) {
                case '18-25':
                    $topProductsQuery->whereBetween('customers.age', [18, 25]);
                    break;
                case '26-35':
                    $topProductsQuery->whereBetween('customers.age', [26, 35]);
                    break;
                case '36-50':
                    $topProductsQuery->whereBetween('customers.age', [36, 50]);
                    break;
                case '51+':
                    $topProductsQuery->where('customers.age', '>=', 51);
                    break;
            }
        }

        $topProducts = $topProductsQuery
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
        $categoryQuery = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo]);

        // Apply the same filters to category query
        if ($region) {
            $categoryQuery->join('customers', 'sales.customer_id', '=', 'customers.id')
                         ->where('customers.region', $region);
        }
        if ($customerId) {
            $categoryQuery->where('sales.customer_id', $customerId);
        }
        if ($productId) {
            $categoryQuery->where('sales.product_id', $productId);
        }
        if ($minQuantity) {
            $categoryQuery->where('sales.quantity', '>=', $minQuantity);
        }
        if ($maxQuantity) {
            $categoryQuery->where('sales.quantity', '<=', $maxQuantity);
        }
        if ($minAmount) {
            $categoryQuery->whereRaw('(sales.quantity * products.price) >= ?', [$minAmount]);
        }
        if ($maxAmount) {
            $categoryQuery->whereRaw('(sales.quantity * products.price) <= ?', [$maxAmount]);
        }
        if ($gender) {
            if (!$region) {
                $categoryQuery->join('customers', 'sales.customer_id', '=', 'customers.id');
            }
            $categoryQuery->where('customers.gender', $gender);
        }
        if ($ageRange) {
            if (!$region && !$gender) {
                $categoryQuery->join('customers', 'sales.customer_id', '=', 'customers.id');
            }
            switch ($ageRange) {
                case '18-25':
                    $categoryQuery->whereBetween('customers.age', [18, 25]);
                    break;
                case '26-35':
                    $categoryQuery->whereBetween('customers.age', [26, 35]);
                    break;
                case '36-50':
                    $categoryQuery->whereBetween('customers.age', [36, 50]);
                    break;
                case '51+':
                    $categoryQuery->where('customers.age', '>=', 51);
                    break;
            }
        }

        $salesByCategory = $categoryQuery
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
        $regionalQuery = Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo]);

        // Apply the same filters to regional query
        if ($category) {
            $regionalQuery->where('products.category', $category);
        }
        if ($customerId) {
            $regionalQuery->where('sales.customer_id', $customerId);
        }
        if ($productId) {
            $regionalQuery->where('sales.product_id', $productId);
        }
        if ($minQuantity) {
            $regionalQuery->where('sales.quantity', '>=', $minQuantity);
        }
        if ($maxQuantity) {
            $regionalQuery->where('sales.quantity', '<=', $maxQuantity);
        }
        if ($minAmount) {
            $regionalQuery->whereRaw('(sales.quantity * products.price) >= ?', [$minAmount]);
        }
        if ($maxAmount) {
            $regionalQuery->whereRaw('(sales.quantity * products.price) <= ?', [$maxAmount]);
        }
        if ($gender) {
            $regionalQuery->where('customers.gender', $gender);
        }
        if ($ageRange) {
            switch ($ageRange) {
                case '18-25':
                    $regionalQuery->whereBetween('customers.age', [18, 25]);
                    break;
                case '26-35':
                    $regionalQuery->whereBetween('customers.age', [26, 35]);
                    break;
                case '36-50':
                    $regionalQuery->whereBetween('customers.age', [36, 50]);
                    break;
                case '51+':
                    $regionalQuery->where('customers.age', '>=', 51);
                    break;
            }
        }

        $regionalPerformance = $regionalQuery
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

    public function getChartData(Request $request)
    {
        $period = $request->get('period', '30d');
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

        // Calculate date range based on period
        $now = now();
        switch ($period) {
            case '7d':
                $dateFrom = $now->copy()->subDays(7);
                $dateTo = $now;
                break;
            case '30d':
                $dateFrom = $now->copy()->subDays(30);
                $dateTo = $now;
                break;
            case '90d':
                $dateFrom = $now->copy()->subDays(90);
                $dateTo = $now;
                break;
            case '1y':
                $dateFrom = $now->copy()->subYear();
                $dateTo = $now;
                break;
            default:
                $dateFrom = $now->copy()->subDays(30);
                $dateTo = $now;
        }

        // Generate chart data with filters
        $chartData = $this->generateChartData(
            $dateFrom, $dateTo, $category, $region, $customerId, $productId,
            $minQuantity, $maxQuantity, $minAmount, $maxAmount, $gender, $ageRange
        );

        // Add conversion rate to chart data
        $chartData['conversionRate'] = $this->calculateConversionRate(
            $dateFrom, $dateTo, $category, $region, $customerId, $productId,
            $minQuantity, $maxQuantity, $minAmount, $maxAmount, $gender, $ageRange
        );

        return response()->json($chartData);
    }
} 