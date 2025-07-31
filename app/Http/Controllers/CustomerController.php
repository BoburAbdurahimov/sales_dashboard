<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::all();
    }

    public function show($id)
    {
        return Customer::findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2',
            'email' => 'required|email|unique:customers,email|max:255',
            'phone_number' => 'required|string|max:20|min:10',
            'age' => 'required|integer|min:1|max:120',
            'gender' => 'required|in:male,female,other',
            'region' => 'required|string|max:100',
            'address' => 'required|string|max:500',
        ]);
        $customer = Customer::create($validated);
        return response()->json($customer, 201);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|min:2',
            'email' => 'sometimes|required|email|unique:customers,email,' . $id . '|max:255',
            'phone_number' => 'sometimes|required|string|max:20|min:10',
            'age' => 'sometimes|required|integer|min:1|max:120',
            'gender' => 'sometimes|required|in:male,female,other',
            'region' => 'sometimes|required|string|max:100',
            'address' => 'sometimes|required|string|max:500',
        ]);
        $customer->update($validated);
        return response()->json($customer);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return response()->json(null, 204);
    }
} 