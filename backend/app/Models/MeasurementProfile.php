<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeasurementProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'height', 'chest', 'waist', 'hip', 'shoulder',
        'sleeve', 'neck', 'shirt_length', 'trouser_length', 'wrist', 'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
