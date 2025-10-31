<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_customer_id',
        'delivery_id',
        'entry_number',
        'transaction_date',
        'entry_origin',
        'debit_amount',
        'credit_amount',
        'balance',
        'description',
        'transaction_type'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'balance' => 'decimal:2'
    ];

    public function deliveryCustomer(): BelongsTo
    {
        return $this->belongsTo(DeliveryCustomer::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    /**
     * Get the next entry number for a delivery customer
     */
    public static function getNextEntryNumber(): string
    {
        $lastEntry = static::orderBy('id', 'desc')->first();
        if (!$lastEntry) {
            return '0001';
        }
        
        $lastNumber = (int) $lastEntry->entry_number;
        return str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get delivery customer's current balance
     */
    public static function getDeliveryCustomerBalance($deliveryCustomerId): float
    {
        $lastEntry = static::where('delivery_customer_id', $deliveryCustomerId)
                          ->orderBy('transaction_date', 'desc')
                          ->orderBy('id', 'desc')
                          ->first();
        
        return $lastEntry ? (float) $lastEntry->balance : 0.00;
    }

    /**
     * Create a new ledger entry and update running balance
     */
    public static function createEntry(array $data): static
    {
        $deliveryCustomerId = $data['delivery_customer_id'];
        $currentBalance = static::getDeliveryCustomerBalance($deliveryCustomerId);
        
        $debitAmount = (float) ($data['debit_amount'] ?? 0);
        $creditAmount = (float) ($data['credit_amount'] ?? 0);
        
        // Calculate new balance (negative means delivery customer owes money)
        $newBalance = $currentBalance - $creditAmount + $debitAmount;
        
        $ledgerData = array_merge($data, [
            'entry_number' => $data['entry_number'] ?? static::getNextEntryNumber(),
            'balance' => $newBalance
        ]);

        return static::create($ledgerData);
    }
}

