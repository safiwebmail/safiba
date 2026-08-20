<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $table = 'income';

    protected $fillable = ['shop_id', 'category', 'amount', 'date', 'description', 'reference', 'added_by'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
