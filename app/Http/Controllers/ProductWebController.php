<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('category', 'like', "%$search%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Filter by stock level
        if ($request->filled('stock_level')) {
            $stockLevel = $request->input('stock_level');
            switch ($stockLevel) {
                case 'in_stock':
                    $query->where('quantity', '>', 0);
                    break;
                case 'low_stock':
                    $query->where('quantity', '>', 0)->where('quantity', '<=', 10);
                    break;
                case 'out_of_stock':
                    $query->where('quantity', '=', 0);
                    break;
            }
        }

        // Sorting
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');

        // Validate the sort column
        $allowedSorts = ['id', 'name', 'category', 'price', 'quantity', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }
        $query->orderBy($sort, $direction);

        // Get paginated results
        $products = $query->paginate(15)->withQueryString();

        // For filter dropdowns
        $categories = Product::select('category')->distinct()->pluck('category');

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2|unique:products,name',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'quantity' => 'required|integer|min:0|max:999999',
        ], [
            'name.required' => 'Product name is required.',
            'name.min' => 'Product name must be at least 2 characters.',
            'name.max' => 'Product name cannot exceed 255 characters.',
            'name.unique' => 'This product name already exists.',
            'category.required' => 'Product category is required.',
            'category.max' => 'Category cannot exceed 100 characters.',
            'price.required' => 'Product price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be at least $0.01.',
            'price.max' => 'Price cannot exceed $999,999.99.',
            'quantity.required' => 'Product quantity is required.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity cannot be negative.',
            'quantity.max' => 'Quantity cannot exceed 999,999.',
        ]);
        
        Product::create($validated);
        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2|unique:products,name,' . $id,
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'quantity' => 'required|integer|min:0|max:999999',
        ], [
            'name.required' => 'Product name is required.',
            'name.min' => 'Product name must be at least 2 characters.',
            'name.max' => 'Product name cannot exceed 255 characters.',
            'name.unique' => 'This product name already exists.',
            'category.required' => 'Product category is required.',
            'category.max' => 'Category cannot exceed 100 characters.',
            'price.required' => 'Product price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be at least $0.01.',
            'price.max' => 'Price cannot exceed $999,999.99.',
            'quantity.required' => 'Product quantity is required.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity cannot be negative.',
            'quantity.max' => 'Quantity cannot exceed 999,999.',
        ]);
        
        $product->update($validated);
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
} 