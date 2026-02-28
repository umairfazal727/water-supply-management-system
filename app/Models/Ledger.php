<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ledger extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_id',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the next entry number for a customer
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
     * Get customer's current balance
     */
    public static function getCustomerBalance($customerId): float
    {
        $lastEntry = static::where('customer_id', $customerId)
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
        $customerId = $data['customer_id'];
        $currentBalance = static::getCustomerBalance($customerId);
        
        $debitAmount = (float) ($data['debit_amount'] ?? 0);
        $creditAmount = (float) ($data['credit_amount'] ?? 0);
        
        // Calculate new balance (negative means customer owes money)
        $newBalance = $currentBalance - $creditAmount + $debitAmount;
        
        $ledgerData = array_merge($data, [
            'entry_number' => $data['entry_number'] ?? static::getNextEntryNumber(),
            'balance' => $newBalance
        ]);

        return static::create($ledgerData);
    }

    /**
     * Recalculate all ledger balances for a customer (e.g. after edit or delete of an entry).
     */
    public static function recalculateBalancesForCustomer($customerId): void
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            return;
        }

        $ledgers = static::where('customer_id', $customerId)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = (float) ($customer->opening_balance ?? 0);

        foreach ($ledgers as $ledger) {
            $runningBalance = $runningBalance - (float) $ledger->credit_amount + (float) $ledger->debit_amount;
            $ledger->balance = $runningBalance;
            $ledger->saveQuietly();
        }
    }
}
