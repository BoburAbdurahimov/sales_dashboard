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

        return view('sales.reports', [
            'salesByCategory' => $salesByCategory,
            'salesByRegion' => $salesByRegion,
            'salesOverTime' => $salesOverTime,
            'recentSales' => $recentSales,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'totalCustomers' => $totalCustomers,
            'totalProducts' => $totalProducts,
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
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

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
            $salesQuery->where('quantity', '>=', $minQuantity);
        }
        if ($maxQuantity) {
            $salesQuery->where('quantity', '<=', $maxQuantity);
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
        $totalRevenue = Sale::join('products', 'sales.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateFrom, $dateTo]);
        
        // Apply the same filters to revenue calculation
        if ($category) {
            $totalRevenue->whereHas('product', function ($query) use ($category) {
                $query->where('category', $category);
            });
        }
        if ($region) {
            $totalRevenue->whereHas('customer', function ($query) use ($region) {
                $query->where('region', $region);
            });
        }
        if ($customerId) {
            $totalRevenue->where('customer_id', $customerId);
        }
        if ($productId) {
            $totalRevenue->where('product_id', $productId);
        }
        if ($minQuantity) {
            $totalRevenue->where('quantity', '>=', $minQuantity);
        }
        if ($maxQuantity) {
            $totalRevenue->where('quantity', '<=', $maxQuantity);
        }
        if ($gender) {
            $totalRevenue->whereHas('customer', function ($query) use ($gender) {
                $query->where('gender', $gender);
            });
        }
        if ($ageRange) {
            $totalRevenue->whereHas('customer', function ($query) use ($ageRange) {
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
        
        $totalRevenue = $totalRevenue->sum(DB::raw('sales.quantity * products.price'));
        
        $totalOrders = $salesQuery->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $conversionRate = 3.2; // This would be calculated based on your business logic

        return view('sales.reports', [
            'salesData' => $salesData,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'avgOrderValue' => $avgOrderValue,
            'conversionRate' => $conversionRate,
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
} 