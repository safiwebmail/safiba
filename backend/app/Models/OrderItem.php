<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'name', 'quantity', 'price', 'total',
        'size', 'color', 'garment_type', 'fabric', 'measurement_profile_id',
        'measurements', 'design_image', 'instructions',
    ];

    protected function casts(): array
    {
        return [
            'measurements' => 'array',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
