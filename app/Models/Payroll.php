<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payroll';

    protected $fillable = ['employee_id', 'shop_id', 'base_salary', 'bonus', 'deduction', 'net_salary', 'payment_date', 'notes', 'added_by'];

    protected function casts(): array
    {
        return ['payment_date' => 'date'];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
