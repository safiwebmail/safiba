<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'shop_id', 'customer_name', 'customer_phone',
        'customer_address', 'type', 'status', 'payment_status', 'payment_method',
        'subtotal', 'discount', 'delivery_fee', 'total', 'delivery_method',
        'notes', 'tailor_id', 'expected_completion_date', 'assigned_at',
        'completed_at', 'cancelled_at', 'cancelled_reason',
    ];

    protected function casts(): array
    {
        return [
            'expected_completion_date' => 'date',
            'assigned_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public const STATUSES = [
        'pending', 'confirmed', 'assigned', 'cutting', 'stitching',
        'quality_check', 'ready', 'delivered', 'completed', 'cancelled',
    ];

    public const PAYMENT_STATUSES = ['unpaid', 'partial', 'paid', 'refunded'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tailor()
    {
        return $this->belongsTo(User::class, 'tailor_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function scopeForShop($query, array $shopIds)
    {
        return $query->whereIn('shop_id', $shopIds);
    }

    public function isCustom(): bool
    {
        return $this->type === 'custom';
    }
}
