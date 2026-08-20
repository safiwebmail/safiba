<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'logo', 'phone', 'email', 'whatsapp', 'address',
        'currency', 'timezone', 'default_delivery_fee',
        'order_prefix', 'invoice_prefix',
    ];
}
