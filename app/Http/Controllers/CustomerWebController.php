<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone_number', 'like', "%$search%")
                  ->orWhere('region', 'like', "%$search%")
                  ->orWhere('address', 'like', "%$search%")
                  ->orWhere('gender', 'like', "%$search%")
                  ->orWhere('age', 'like', "%$search%") ;
            });
        }

        // Filter by region
        if ($request->filled('region')) {
            $query->where('region', $request->input('region'));
        }
        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }
        // Sorting
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');

        // Validate the sort column
        $allowedSorts = ['id', 'name', 'email', 'phone_number', 'region', 'gender', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }

        $query->orderBy($sort, $direction);

        // Get all results without pagination
        $customers = $query->get();

        // For filter dropdowns
        $regions = Customer::select('region')->distinct()->pluck('region');
        $genders = ['male', 'female', 'other'];

        return view('customers.index', compact('customers', 'regions', 'genders'));
    }

    public function create()
    {
        return view('customers.create');
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
        ], [
            'name.required' => 'Customer name is required.',
            'name.min' => 'Customer name must be at least 2 characters.',
            'name.max' => 'Customer name cannot exceed 255 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone_number.required' => 'Phone number is required.',
            'phone_number.min' => 'Phone number must be at least 10 characters.',
            'phone_number.max' => 'Phone number cannot exceed 20 characters.',
            'age.required' => 'Age is required.',
            'age.integer' => 'Age must be a number.',
            'age.min' => 'Age must be at least 1.',
            'age.max' => 'Age cannot exceed 120.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Please select a valid gender.',
            'region.required' => 'Region is required.',
            'region.max' => 'Region cannot exceed 100 characters.',
            'address.required' => 'Address is required.',
            'address.max' => 'Address cannot exceed 500 characters.',
        ]);
        
        Customer::create($validated);
        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2',
            'email' => 'required|email|unique:customers,email,' . $id . '|max:255',
            'phone_number' => 'required|string|max:20|min:10',
            'age' => 'required|integer|min:1|max:120',
            'gender' => 'required|in:male,female,other',
            'region' => 'required|string|max:100',
            'address' => 'required|string|max:500',
        ], [
            'name.required' => 'Customer name is required.',
            'name.min' => 'Customer name must be at least 2 characters.',
            'name.max' => 'Customer name cannot exceed 255 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone_number.required' => 'Phone number is required.',
            'phone_number.min' => 'Phone number must be at least 10 characters.',
            'phone_number.max' => 'Phone number cannot exceed 20 characters.',
            'age.required' => 'Age is required.',
            'age.integer' => 'Age must be a number.',
            'age.min' => 'Age must be at least 1.',
            'age.max' => 'Age cannot exceed 120.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Please select a valid gender.',
            'region.required' => 'Region is required.',
            'region.max' => 'Region cannot exceed 100 characters.',
            'address.required' => 'Address is required.',
            'address.max' => 'Address cannot exceed 500 characters.',
        ]);
        
        $customer->update($validated);
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
} 