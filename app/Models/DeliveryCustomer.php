<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'delivery_location',
        'opening_balance',
        'rate',
        'sweet_water_price',
        'salt_water_price',
        'drinking_water_price',
        'is_active'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'rate' => 'decimal:2',
        'sweet_water_price' => 'decimal:2',
        'salt_water_price' => 'decimal:2',
        'drinking_water_price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(DeliveryLedger::class);
    }

    /**
     * Get current balance including opening balance and all ledger entries
     */
    public function getCurrentBalance()
    {
        $openingBalance = (float) $this->opening_balance;
        $ledgerBalance = DeliveryLedger::getDeliveryCustomerBalance($this->id);
        return $openingBalance + $ledgerBalance;
    }

    /**
     * Create opening balance ledger entry if needed
     */
    public function createOpeningBalanceEntry()
    {
        if ($this->opening_balance != 0) {
            // Check if opening balance entry already exists
            $existingEntry = $this->ledgers()
                ->where('transaction_type', 'opening_balance')
                ->first();

            if (!$existingEntry) {
                DeliveryLedger::createEntry([
                    'delivery_customer_id' => $this->id,
                    'transaction_date' => now()->startOfDay(),
                    'entry_origin' => 'OB',
                    'debit_amount' => 0,
                    'credit_amount' => $this->opening_balance,
                    'description' => 'Opening Balance',
                    'transaction_type' => 'opening_balance'
                ]);
            }
        }
    }
}
