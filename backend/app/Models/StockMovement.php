<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = ['inventory_id', 'shop_id', 'type', 'quantity', 'balance', 'reason', 'reference', 'user_id'];

    public function inventory()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_id');
    }
}
