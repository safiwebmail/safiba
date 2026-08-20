<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id', 'name', 'sku', 'category', 'quantity', 'unit',
        'cost', 'selling_price', 'min_stock', 'status',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class, 'inventory_id');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->min_stock > 0 && $this->quantity <= $this->min_stock;
    }
}
