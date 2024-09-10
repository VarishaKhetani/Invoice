<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class InvoiceController extends Controller
{
    public function index()
    {
        // Retrieve all invoices with their related products
        $invoices = Invoice::with('products')->get()->map(function ($invoice) {
            // Calculate total amount, discount amount, and final bill
            $totalAmount = $invoice->products->sum('price');
            $totalDiscountAmount = $invoice->products->sum('discount_amount');
            $totalBill = $invoice->products->sum('final_price');

            // Assign these values to the invoice object
            $invoice->total_amount = $totalAmount;
            $invoice->total_discount_amount = $totalDiscountAmount;
            $invoice->total_bill = $totalBill;

            return $invoice;
        });

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        return view('invoices.create');
    }

   
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255|unique:invoices',
            'products' => 'required|array',
            'products.*.name' => 'required|string|max:255',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            // Create an Invoice
            $invoice = new Invoice();
            $invoice->customer_name = $request->customer_name;
            $invoice->customer_email = $request->customer_email;
            $invoice->total_items = count($request->products);
            $invoice->total_amount = 0;
            $invoice->total_discount_amount = 0;
            $invoice->total_bill = 0;
            $invoice->save();

            // Save each product
            foreach ($request->products as $productData) {
                $price = $productData['price'];
                $discount = $productData['discount'] ?? 0;
                $discountAmount = ($price * $discount) / 100;
                $finalPrice = $price - $discountAmount;

                $product = new Product();
                $product->invoice_id = $invoice->id;
                $product->name = $productData['name'];
                $product->price = $price;
                $product->discount = $discount;
                $product->discount_amount = $discountAmount;
                $product->final_price = $finalPrice;
                $product->save();

                // Update the invoice totals
                $invoice->total_amount += $price;
                $invoice->total_discount_amount += $discountAmount;
                $invoice->total_bill += $finalPrice;
            }

            // Save invoice totals
            $invoice->save();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully!',
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            // Handle exception and return error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to create the invoice. Please try again.'
            ], 500);
        }
    }
    public function show($id)
    {
        // Retrieve the invoice with its associated products
        $invoice = Invoice::with('products')->find($id);

        // Check if the invoice exists
        if (!$invoice) {
            return redirect()->route('invoices.index')->with('error', 'Invoice not found.');
        }

        // Pass the invoice data to the view
        return view('invoices.show', compact('invoice'));
    }
    public function edit($id)
    {
        $invoice = Invoice::with('products')->findOrFail($id);
        return view('invoices.edit', compact('invoice'));
    }


    public function update(Request $request, $invoiceId)
    {
        // Validate the request
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'products.*.name' => 'required|string',
            'products.*.price' => 'required|numeric',
            'products.*.discount' => 'nullable|numeric|min:0', // Ensure discount is non-negative
            'products.*.id' => 'nullable|integer|exists:products,id', // Ensure product ID exists
            'products.*.delete' => 'nullable|boolean',
            'invoice_id' => 'required|integer|exists:invoices,id'
        ]);

        // Find the invoice
        $invoice = Invoice::findOrFail($invoiceId);

        // Update invoice details
        $invoice->customer_name = $request->input('customer_name');
        $invoice->customer_email = $request->input('customer_email');
        $invoice->save();

        // Get all product IDs that are currently associated with the invoice
        $existingProductIds = $invoice->products->pluck('id')->toArray();

        // Handle products
        $products = $request->input('products');

        // Remove products marked for deletion
        foreach ($products as $product) {
            if (isset($product['delete']) && $product['delete'] == 'true') {
                $productModel = Product::find($product['id']);
                if ($productModel) {
                    $productModel->delete();
                    // Remove from existing product IDs to prevent re-adding
                    $existingProductIds = array_diff($existingProductIds, [$product['id']]);
                }
            }
        }

        // Add or update products
        foreach ($products as $product) {
            if (!isset($product['delete']) || $product['delete'] == 'false') {
                // Calculate discount amount and final price
                $price = $product['price'];
                $discount = $product['discount'] ?? 0;
                $discountAmount = ($price * $discount) / 100;
                $finalPrice = $price - $discountAmount;

                // Update or create product
                $productModel = Product::updateOrCreate(
                    ['id' => $product['id'] ?? null],
                    [
                        'name' => $product['name'],
                        'price' => $price,
                        'discount' => $discount,
                        'discount_amount' => $discountAmount,
                        'final_price' => $finalPrice,
                        'invoice_id' => $invoice->id
                    ]
                );

                // Associate product with invoice
                $invoice->products()->save($productModel);

                // Remove from existing product IDs as it is now updated
                $existingProductIds = array_diff($existingProductIds, [$productModel->id]);
            }
        }

        // Delete any remaining products that were not included in the update
        Product::whereIn('id', $existingProductIds)->delete();

        return response()->json(['success' => true, 'message' => 'Invoice updated successfully']);

    }


    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }
    }
}
