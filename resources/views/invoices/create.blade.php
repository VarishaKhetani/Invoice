@extends('layouts.app')
@section('content')
    <div class="container mt-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="mb-0">Invoice Form</h1>
            <a href="{{ url('/invoices/') }}" class="btn btn-danger">Back</a>
        </div>
        
        <!-- Add an alert for success or error -->
        <div id="alert-container"></div>

        <form id="invoice-form">
            <!-- CSRF Token -->
            <input type="hidden" name="_token" id="csrf-token" value="{{ csrf_token() }}">

            <!-- Customer Information -->
            <div class="mb-3">
                <label for="customer_name" class="form-label">Customer Name</label>
                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
            </div>

            <div class="mb-3">
                <label for="customer_email" class="form-label">Customer Email</label>
                <input type="email" class="form-control" id="customer_email" name="customer_email" required>
            </div>

            <!-- Product Table -->
            <label for="products" class="form-label">Products</label>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Discount (%)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="product-list">
                    <tr>
                        <td><input type="text" class="form-control" name="products[0][name]" required></td>
                        <td><input type="number" class="form-control product-price" name="products[0][price]" required>
                        </td>
                        <td><input type="number" class="form-control product-discount" name="products[0][discount]">
                        </td>
                        <td><button type="button" class="btn btn-danger remove-product">Remove</button></td>
                    </tr>
                </tbody>
            </table>

            <button type="button" class="btn btn-secondary" id="add-product">Add Product</button>

            <!-- Summary Section -->
            <div class="mt-4">
                <div class="form-group">
                    <label>Total Items</label>
                    <input type="text" class="form-control" id="total-items" readonly>
                </div>

                <div class="form-group mt-2">
                    <label>Total Amount</label>
                    <input type="text" class="form-control" id="total-amount" readonly>
                </div>

                <div class="form-group mt-2">
                    <label>Total Discount Amount</label>
                    <input type="text" class="form-control" id="total-discount-amount" readonly>
                </div>

                <div class="form-group mt-2">
                    <label>Total Bill</label>
                    <input type="text" class="form-control" id="total-bill" readonly>
                </div>
            </div>

            <div class="mt-3">
                <button type="button" id="submit-btn" class="btn btn-primary">Save</button>
                <a href="{{ url('/invoices/') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        let productIndex = 1;
    
        // Add new product row
        document.getElementById('add-product').addEventListener('click', function() {
            const productRow = `
                <tr>
                    <td><input type="text" class="form-control" name="products[${productIndex}][name]" required></td>
                    <td><input type="number" class="form-control product-price" name="products[${productIndex}][price]" required></td>
                    <td><input type="number" class="form-control product-discount" name="products[${productIndex}][discount]"></td>
                    <td><button type="button" class="btn btn-danger remove-product">Remove</button></td>
                </tr>
            `;
            document.getElementById('product-list').insertAdjacentHTML('beforeend', productRow);
            productIndex++;
            updateSummary();
        });
    
        // Remove product row
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-product')) {
                event.target.closest('tr').remove();
                updateSummary();
            }
        });
    
        // Update the summary section whenever a price or discount is changed
        document.addEventListener('input', function(event) {
            if (event.target.classList.contains('product-price') || event.target.classList.contains('product-discount')) {
                updateSummary();
            }
        });
    
        // Calculate and update the summary
        function updateSummary() {
            let totalItems = 0;
            let totalAmount = 0;
            let totalDiscountAmount = 0;
            let totalBill = 0;
    
            const prices = document.querySelectorAll('.product-price');
            const discounts = document.querySelectorAll('.product-discount');
    
            prices.forEach((priceInput, index) => {
                const price = parseFloat(priceInput.value) || 0;
                const discount = parseFloat(discounts[index].value) || 0;
    
                totalItems++;
                totalAmount += price;
                totalDiscountAmount += (price * discount) / 100;
                totalBill += price - (price * discount) / 100;
            });
    
            document.getElementById('total-items').value = totalItems;
            document.getElementById('total-amount').value = totalAmount.toFixed(2);
            document.getElementById('total-discount-amount').value = totalDiscountAmount.toFixed(2);
            document.getElementById('total-bill').value = totalBill.toFixed(2);
        }
    
        // AJAX form submission
        document.getElementById('submit-btn').addEventListener('click', function(e) {
            e.preventDefault();
    
            let formData = new FormData(document.getElementById('invoice-form'));
    
            fetch("{{ url('/invoices') }}", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': document.getElementById('csrf-token').value,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    let alertContainer = document.getElementById('alert-container');
                    if (data.success) {
                        alertContainer.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        setTimeout(() => {
                            window.location.href = "{{ url('/invoices') }}"; // Redirect to the index page
                        }, 1000); // Delay redirect to show the success message
                    } else if (data.errors) {
                        // Display validation errors
                        let errors = Object.values(data.errors).flat().join('<br>');
                        alertContainer.innerHTML = `<div class="alert alert-danger">${errors}</div>`;
                    } else {
                        alertContainer.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('alert-container').innerHTML =
                        `<div class="alert alert-danger">An error occurred. Please try again.</div>`;
                });
        });
    
        // Initial call to update the summary when the page loads
        updateSummary();
    </script>
    
@endsection