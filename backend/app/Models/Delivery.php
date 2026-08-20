<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'shop_id', 'method', 'address', 'fee', 'status', 'staff_id', 'delivery_date', 'notes'];

    protected function casts(): array
    {
        return ['delivery_date' => 'date'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
