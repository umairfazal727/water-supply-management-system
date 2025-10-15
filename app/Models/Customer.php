<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'avatar',
        'user_id',
        'vehicle_id',
        'driver_id',
        'company_name',
        'tanker_size',
        'product_type', // sweet_water or salt_water
        'price',
        'opening_balance',
    ];

    public function getAvatarUrl()
    {
        return Storage::url($this->avatar);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }

    /**
     * Get current balance including opening balance and all ledger entries
     */
    public function getCurrentBalance()
    {
        $openingBalance = (float) $this->opening_balance;
        $ledgerBalance = Ledger::getCustomerBalance($this->id);
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
                Ledger::createEntry([
                    'customer_id' => $this->id,
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
