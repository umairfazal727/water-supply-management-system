<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportEmployeeSalaryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transport_employee_id',
        'transaction_number',
        'transaction_date',
        'transaction_type',
        'amount',
        'payment_method',
        'reference_number',
        'description',
        'month',
        'year',
        'is_advance',
        'balance_after',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'is_advance' => 'boolean',
        'year' => 'integer',
    ];

    /**
     * Generate unique transaction number
     */
    public static function generateTransactionNumber(): string
    {
        $lastTransaction = static::orderBy('id', 'desc')->first();
        $number = $lastTransaction ? ((int) substr($lastTransaction->transaction_number, 5)) + 1 : 1;
        return 'TEST' . str_pad($number, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function transportEmployee(): BelongsTo
    {
        return $this->belongsTo(TransportEmployee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
