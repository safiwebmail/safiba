<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = ['purchase_id', 'inventory_id', 'name', 'quantity', 'unit_cost', 'total'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
