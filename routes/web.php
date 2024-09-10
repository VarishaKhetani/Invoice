<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::get('/invoices/create', [InvoiceController::class, 'create']);
Route::post('/invoices', [InvoiceController::class, 'store']);
Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
Route::get('/invoices/{id}/edit', [InvoiceController::class, 'edit']);
Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
Route::delete('/products/{id}', [InvoiceController::class, 'destroy']);