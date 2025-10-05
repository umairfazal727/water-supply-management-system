<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'expense_category_id',
        'user_id',
        'driver_id',
        'vehicle_id',
        'title',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'reference_number',
        'attachments',
        'is_approved',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'attachments' => 'array',
        'expense_date' => 'date',
        'approved_at' => 'datetime',
        'is_approved' => 'boolean'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}