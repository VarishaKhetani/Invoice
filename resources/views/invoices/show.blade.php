@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="mb-0">Show Invoice Details</h1>
        <a href="{{ url('/invoices/') }}" class="btn btn-danger">Back</a>
    </div>
    
    <div class="invoice-summary mb-4 p-3 border rounded">
        <h3>Customer Name: <span class="text-primary">{{ $invoice->customer_name }}</span></h3>
        <h4>Customer Email: <span class="text-muted">{{ $invoice->customer_email }}</span></h4>
    </div>
    @if($invoice->products->isEmpty())
        <p class="alert alert-info">No products available for this invoice.</p>
    @else
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Discount Amount</th>
                    <th>Final Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ number_format($product->price, 2) }}</td>
                    <td>{{ number_format($product->discount, 2) }}%</td>
                    <td>{{ number_format(($product->price * $product->discount) / 100, 2) }}</td>
                    <td>{{ number_format($product->price - ($product->price * $product->discount) / 100, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            
        </table>
    @endif
</div>
@endsection
