<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function show($id)
    {
        return Product::findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2|unique:products,name',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'quantity' => 'required|integer|min:0|max:999999',
        ]);
        $product = Product::create($validated);
        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|min:2|unique:products,name,' . $id,
            'category' => 'sometimes|required|string|max:100',
            'price' => 'sometimes|required|numeric|min:0.01|max:999999.99',
            'quantity' => 'sometimes|required|integer|min:0|max:999999',
        ]);
        $product->update($validated);
        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(null, 204);
    }
} 