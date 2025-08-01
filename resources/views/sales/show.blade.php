@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Sale Details</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Sale #{{ $sale->id }}</h5>
            <p class="card-text"><strong>Customer:</strong> {{ $sale->customer->name ?? '-' }}</p>
            <p class="card-text"><strong>Product:</strong> {{ $sale->product->name ?? '-' }}</p>
            <p class="card-text"><strong>Quantity:</strong> {{ $sale->quantity }}</p>
            <p class="card-text"><strong>Created At:</strong> {{ $sale->created_at->format('d-m-Y H:i:s') }}</p>
            <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>
@endsection 