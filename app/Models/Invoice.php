<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_name',
        'customer_email',
        'total_items',
        'total_amount',
        'total_discount_amount',
        'total_bill',
    ];
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
}
