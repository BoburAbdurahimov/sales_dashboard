<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'product']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            })->orWhereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('category', 'like', "%$search%");
            })->orWhere('quantity', 'like', "%$search%");
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Filter by quantity range
        if ($request->filled('min_quantity')) {
            $query->where('quantity', '>=', $request->input('min_quantity'));
        }
        if ($request->filled('max_quantity')) {
            $query->where('quantity', '<=', $request->input('max_quantity'));
        }

        // Sorting
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        // Validate the sort column
        $allowedSorts = ['id', 'customer_name', 'product_name', 'quantity', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        if ($sort === 'customer_name') {
            $query->select('sales.*', 'customers.name as customer_name')
                  ->join('customers', 'sales.customer_id', '=', 'customers.id')
                  ->orderBy('customer_name', $direction);
        } elseif ($sort === 'product_name') {
            $query->select('sales.*', 'products.name as product_name')
                  ->join('products', 'sales.product_id', '=', 'products.id')
                  ->orderBy('product_name', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        // Get all results without pagination
        $sales = $query->get();

        // For filter dropdowns
        $customers = Customer::select('id', 'name')->orderBy('name')->get();
        $products = Product::select('id', 'name')->orderBy('name')->get();

        return view('sales.index', compact('sales', 'customers', 'products'));
    }

    public function create()
    {
        $customers = Customer::all();
        $products = Product::all();
        $categories = Product::select('category')->distinct()->pluck('category');
        
        // Handle pre-selected product from URL parameter
        $selectedProductId = request('product_id');
        
        return view('sales.create', compact('customers', 'products', 'categories', 'selectedProductId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:999999',
        ], [
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'The selected customer does not exist.',
            'product_id.required' => 'Please select a product.',
            'product_id.exists' => 'The selected product does not exist.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Quantity cannot exceed 999,999.',
        ]);
        
        // Check if product has enough stock
        $product = Product::find($validated['product_id']);
        if ($product->quantity < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Insufficient stock. Available: ' . $product->quantity])->withInput();
        }
        
        // Create the sale
        $sale = Sale::create($validated);
        
        // Update product stock
        $product->decrement('quantity', $validated['quantity']);
        
        return redirect()->route('sales.index')->with('success', 'Sale created successfully.');
    }

    public function show($id)
    {
        $sale = Sale::with(['customer', 'product'])->findOrFail($id);
        return view('sales.show', compact('sale'));
    }

    public function edit($id)
    {
        $sale = Sale::findOrFail($id);
        $customers = Customer::all();
        $products = Product::all();
        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:999999',
        ], [
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'The selected customer does not exist.',
            'product_id.required' => 'Please select a product.',
            'product_id.exists' => 'The selected product does not exist.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Quantity cannot exceed 999,999.',
        ]);
        
        // Handle stock adjustment
        $oldQuantity = $sale->quantity;
        $newQuantity = $validated['quantity'];
        $quantityDifference = $newQuantity - $oldQuantity;
        
        // Check if we have enough stock for the increase
        if ($quantityDifference > 0) {
            $product = Product::find($validated['product_id']);
            if ($product->quantity < $quantityDifference) {
                return back()->withErrors(['quantity' => 'Insufficient stock for this increase. Available: ' . $product->quantity])->withInput();
            }
            $product->decrement('quantity', $quantityDifference);
        } elseif ($quantityDifference < 0) {
            // If quantity decreased, add the difference back to stock
            $product = Product::find($validated['product_id']);
            $product->increment('quantity', abs($quantityDifference));
        }
        
        $sale->update($validated);
        return redirect()->route('sales.index')->with('success', 'Sale updated successfully.');
    }

    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        
        // Restore the stock
        $product = Product::find($sale->product_id);
        $product->increment('quantity', $sale->quantity);
        
        $sale->delete();
        return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');
    }
} 