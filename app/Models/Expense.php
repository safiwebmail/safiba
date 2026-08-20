<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'expenses';

    protected $fillable = ['shop_id', 'category', 'amount', 'date', 'description', 'receipt_path', 'added_by'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
