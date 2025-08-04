<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        return Sale::with(['customer', 'product'])->get();
    }

    public function reports(Request $request)
    {
        $query = Sale::with(['customer', 'product']);

        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Category filter via related product
        if ($request->filled('category')) {
            $category = $request->input('category');
            $query->whereHas('product', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }

        // Region filter via related customer
        if ($request->filled('region')) {
            $region = $request->input('region');
            $query->whereHas('customer', function ($q) use ($region) {
                $q->where('region', $region);
            });
        }

        return response()->json($query->get());
    }

    public function export(Request $request)
    {
        try {
            \Log::info('Export request received', [
                'filters' => $request->all(),
                'user_agent' => $request->header('User-Agent')
            ]);
            
            $query = Sale::with(['customer', 'product']);

            // Date range filters - convert from dd-mm-yyyy to Y-m-d format
            if ($request->filled('date_from')) {
                $dateFrom = \Carbon\Carbon::createFromFormat('d-m-Y', $request->input('date_from'))->startOfDay();
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            if ($request->filled('date_to')) {
                $dateTo = \Carbon\Carbon::createFromFormat('d-m-Y', $request->input('date_to'))->endOfDay();
                $query->whereDate('created_at', '<=', $dateTo);
            }

            // Category filter via related product
            if ($request->filled('category')) {
                $category = $request->input('category');
                $query->whereHas('product', function ($q) use ($category) {
                    $q->where('category', $category);
                });
            }

            // Region filter via related customer
            if ($request->filled('region')) {
                $region = $request->input('region');
                $query->whereHas('customer', function ($q) use ($region) {
                    $q->where('region', $region);
                });
            }

            // Customer filter
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->input('customer_id'));
            }

            // Product filter
            if ($request->filled('product_id')) {
                $query->where('product_id', $request->input('product_id'));
            }

            // Quantity range filters
            if ($request->filled('min_quantity')) {
                $query->where('quantity', '>=', $request->input('min_quantity'));
            }
            if ($request->filled('max_quantity')) {
                $query->where('quantity', '<=', $request->input('max_quantity'));
            }

            // Gender filter
            if ($request->filled('gender')) {
                $gender = $request->input('gender');
                $query->whereHas('customer', function ($q) use ($gender) {
                    $q->where('gender', $gender);
                });
            }

            // Age range filter
            if ($request->filled('age_range')) {
                $ageRange = $request->input('age_range');
                $query->whereHas('customer', function ($q) use ($ageRange) {
                    switch ($ageRange) {
                        case '18-25':
                            $q->whereBetween('age', [18, 25]);
                            break;
                        case '26-35':
                            $q->whereBetween('age', [26, 35]);
                            break;
                        case '36-50':
                            $q->whereBetween('age', [36, 50]);
                            break;
                        case '51+':
                            $q->where('age', '>=', 51);
                            break;
                    }
                });
            }

            $sales = $query->get();

            // Apply amount filters after getting the data (since amount is calculated)
            if ($request->filled('min_amount') || $request->filled('max_amount')) {
                $sales = $sales->filter(function ($sale) use ($request) {
                    $amount = $sale->quantity * ($sale->product->price ?? 0);
                    if ($request->filled('min_amount') && $amount < $request->input('min_amount')) return false;
                    if ($request->filled('max_amount') && $amount > $request->input('max_amount')) return false;
                    return true;
                });
            }
            
            \Log::info('Export query completed', [
                'sales_count' => $sales->count(),
                'filters_applied' => $request->all()
            ]);

            // Generate CSV content
            $csvData = [];
            $csvData[] = ['Order ID', 'Customer Name', 'Customer Email', 'Customer Age', 'Customer Gender', 'Customer Region', 'Product Name', 'Category', 'Quantity', 'Unit Price', 'Total Amount', 'Date'];

            foreach ($sales as $sale) {
                $csvData[] = [
                    $sale->id,
                    $sale->customer->name ?? 'N/A',
                    $sale->customer->email ?? 'N/A',
                    $sale->customer->age ?? 'N/A',
                    $sale->customer->gender ?? 'N/A',
                    $sale->customer->region ?? 'N/A',
                    $sale->product->name ?? 'N/A',
                    $sale->product->category ?? 'N/A',
                    $sale->quantity,
                    $sale->product->price ?? 0,
                    ($sale->quantity * ($sale->product->price ?? 0)),
                    $sale->created_at->format('Y-m-d H:i:s')
                ];
            }

            // Convert to CSV string
            $csvContent = '';
            foreach ($csvData as $row) {
                $csvContent .= implode(',', array_map(function($field) {
                    return '"' . str_replace('"', '""', $field) . '"';
                }, $row)) . "\n";
            }

            \Log::info('CSV content generated', [
                'csv_length' => strlen($csvContent),
                'rows_count' => count($csvData)
            ]);

            // Return CSV response with proper headers
            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="sales-report-' . date('Y-m-d') . '.csv"')
                ->header('Cache-Control', 'no-cache, must-revalidate')
                ->header('Pragma', 'no-cache');
                
        } catch (\Exception $e) {
            \Log::error('Export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Export failed: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        return Sale::with(['customer', 'product'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated) {
            // Check if product has enough stock
            $product = Product::findOrFail($validated['product_id']);
            if ($product->quantity < $validated['quantity']) {
                return response()->json([
                    'error' => 'Insufficient stock. Available: ' . $product->quantity . ', Requested: ' . $validated['quantity']
                ], 422);
            }

            // Create the sale
            $sale = Sale::create($validated);

            // Reduce product quantity
            $product->decrement('quantity', $validated['quantity']);

            return response()->json($sale->load(['customer', 'product']), 201);
        });
    }

    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $validated = $request->validate([
            'customer_id' => 'sometimes|required|exists:customers,id',
            'product_id' => 'sometimes|required|exists:products,id',
            'quantity' => 'sometimes|required|integer|min:1',
        ]);

        return DB::transaction(function () use ($sale, $validated) {
            $oldQuantity = $sale->quantity;
            $newQuantity = $validated['quantity'] ?? $oldQuantity;
            $oldProductId = $sale->product_id;
            $newProductId = $validated['product_id'] ?? $oldProductId;

            // If quantity changed, handle stock adjustments
            if (isset($validated['quantity']) && $validated['quantity'] != $oldQuantity) {
                $product = Product::findOrFail($newProductId);
                $quantityDifference = $validated['quantity'] - $oldQuantity;

                // If increasing quantity, check if enough stock is available
                if ($quantityDifference > 0) {
                    if ($product->quantity < $quantityDifference) {
                        return response()->json([
                            'error' => 'Insufficient stock. Available: ' . $product->quantity . ', Additional needed: ' . $quantityDifference
                        ], 422);
                    }
                    $product->decrement('quantity', $quantityDifference);
                } else {
                    // If decreasing quantity, restore the difference
                    $product->increment('quantity', abs($quantityDifference));
                }
            }

            // If product changed, restore old product stock and reduce new product stock
            if (isset($validated['product_id']) && $validated['product_id'] != $oldProductId) {
                $oldProduct = Product::findOrFail($oldProductId);
                $newProduct = Product::findOrFail($validated['product_id']);

                // Restore old product stock
                $oldProduct->increment('quantity', $oldQuantity);

                // Check if new product has enough stock
                if ($newProduct->quantity < $newQuantity) {
                    return response()->json([
                        'error' => 'Insufficient stock in new product. Available: ' . $newProduct->quantity . ', Requested: ' . $newQuantity
                    ], 422);
                }

                // Reduce new product stock
                $newProduct->decrement('quantity', $newQuantity);
            }

            $sale->update($validated);
            return response()->json($sale->load(['customer', 'product']));
        });
    }

    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);

        return DB::transaction(function () use ($sale) {
            // Restore product quantity
            $product = Product::findOrFail($sale->product_id);
            $product->increment('quantity', $sale->quantity);

            $sale->delete();
            return response()->json(null, 204);
        });
    }
} 