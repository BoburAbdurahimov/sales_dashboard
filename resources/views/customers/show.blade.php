@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Customer Details</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $customer->name }}</h5>
            <p class="card-text"><strong>Email:</strong> {{ $customer->email }}</p>
            <p class="card-text"><strong>Phone Number:</strong> {{ $customer->phone_number }}</p>
            <p class="card-text"><strong>Age:</strong> {{ $customer->age }} years old</p>
            <p class="card-text"><strong>Gender:</strong> {{ ucfirst($customer->gender) }}</p>
            <p class="card-text"><strong>Region:</strong> {{ $customer->region }}</p>
            <p class="card-text"><strong>Address:</strong> {{ $customer->address }}</p>
            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>
@endsection 