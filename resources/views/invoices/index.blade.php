@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="mb-0">Invoice List</h1>
        <a href="{{ url('/invoices/create') }}" class="btn btn-danger">Create Invoice</a>
    </div>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer Name</th>
                <th>Total Amount</th>
                <th>Total Discount</th>
                <th>Final Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr>
                <td>{{ $invoice->id }}</td>
                <td>{{ $invoice->customer_name }}</td>
                <td>{{ number_format($invoice->total_amount, 2) }}</td>
                <td>{{ number_format($invoice->total_discount_amount, 2) }}</td>
                <td>{{ number_format($invoice->total_bill, 2) }}</td>
                <td>
                    <a href="{{ url('/invoices/'.$invoice->id) }}" class="btn btn-info">View</a>
                    <a href="{{ url('/invoices/'.$invoice->id.'/edit') }}" class="btn btn-warning">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
