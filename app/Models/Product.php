<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_id',
        'name',
        'price',
        'discount',
        'discount_amount',
        'final_price',
    ];
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
