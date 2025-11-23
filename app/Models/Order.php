<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'total_price',
        'vehicle_number',
        'driver_name',
        'company_name',
        'tanker_size',
        'product_type', // sweet_water or salt_water
        'price',
        'payment_type',
        'branch_id',
        'order_date'
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($order) {
            // Create ledger entry for credit orders
            if (in_array($order->payment_type, ['credit', 'on_account'])) {
                Ledger::createEntry([
                    'customer_id' => $order->customer_id,
                    'order_id' => $order->id,
                    'transaction_date' => $order->order_date ?? now(),
                    'entry_origin' => 'ORDER-' . $order->id,
                    'debit_amount' => 0,
                    'credit_amount' => $order->price,
                    'description' => "Order #{$order->id} - {$order->product_type} - {$order->tanker_size} tanker",
                    'transaction_type' => 'order'
                ]);
            }
        });

        static::updated(function ($order) {
            // Handle ledger updates when order is edited
            $originalPaymentType = $order->getOriginal('payment_type');
            $newPaymentType = $order->payment_type;
            $originalPrice = (float) ($order->getOriginal('price') ?? $order->getOriginal('total_price') ?? 0);
            $newPrice = (float) ($order->price ?? $order->total_price ?? 0);
            
            $isCreditType = fn($type) => in_array($type, ['credit', 'on_account']);
            $wasCredit = $isCreditType($originalPaymentType);
            $isNowCredit = $isCreditType($newPaymentType);
            
            // Find existing ledger entry for this order
            $existingLedger = Ledger::where('order_id', $order->id)->first();
            
            // Scenario 1: Payment type changed from credit/on_account to cash/bank_transfer
            if ($wasCredit && !$isNowCredit && $existingLedger) {
                // Delete the ledger entry
                $customerId = $order->customer_id;
                $existingLedger->delete();
                
                // Recalculate balances for all remaining ledger entries for this customer
                if ($customerId) {
                    static::recalculateCustomerLedgerBalances($customerId);
                }
            }
            // Scenario 2: Payment type changed from cash/bank_transfer to credit/on_account
            elseif (!$wasCredit && $isNowCredit) {
                // Create a new ledger entry
                Ledger::createEntry([
                    'customer_id' => $order->customer_id,
                    'order_id' => $order->id,
                    'transaction_date' => $order->order_date ?? now(),
                    'entry_origin' => 'ORDER-' . $order->id,
                    'debit_amount' => 0,
                    'credit_amount' => $newPrice,
                    'description' => "Order #{$order->id} - {$order->product_type} - {$order->tanker_size} tanker",
                    'transaction_type' => 'order'
                ]);
            }
            // Scenario 3: Payment type stayed as credit/on_account but price changed
            elseif ($wasCredit && $isNowCredit && $existingLedger && $originalPrice != $newPrice) {
                // Update the existing ledger entry
                $existingLedger->credit_amount = $newPrice;
                
                // Update description
                $existingLedger->description = "Order #{$order->id} - {$order->product_type} - {$order->tanker_size} tanker";
                
                // Check if order_date changed
                $originalOrderDate = $order->getOriginal('order_date');
                $newOrderDate = $order->order_date;
                if ($originalOrderDate != $newOrderDate) {
                    $existingLedger->transaction_date = $newOrderDate ?? now();
                }
                
                // Save the ledger entry first
                $existingLedger->saveQuietly();
                
                // Recalculate balances for all ledger entries for this customer
                if ($order->customer_id) {
                    static::recalculateCustomerLedgerBalances($order->customer_id);
                }
            }
            // Scenario 4: Payment type stayed as credit/on_account, price unchanged, but other fields changed
            elseif ($wasCredit && $isNowCredit && $existingLedger && $originalPrice == $newPrice) {
                // Update description
                $existingLedger->description = "Order #{$order->id} - {$order->product_type} - {$order->tanker_size} tanker";
                
                // Check if order_date changed
                $originalOrderDate = $order->getOriginal('order_date');
                $newOrderDate = $order->order_date;
                if ($originalOrderDate != $newOrderDate) {
                    $existingLedger->transaction_date = $newOrderDate ?? now();
                    $existingLedger->saveQuietly();
                    // Recalculate if date changed (affects chronological order)
                    if ($order->customer_id) {
                        static::recalculateCustomerLedgerBalances($order->customer_id);
                    }
                } else {
                    $existingLedger->saveQuietly();
                }
            }
        });

        static::deleting(function ($order) {
            // Delete related ledger entries when order is deleted
            $relatedLedgers = Ledger::where('order_id', $order->id)->get();
            
            if ($relatedLedgers->isNotEmpty()) {
                $customerId = $order->customer_id;
                
                // Delete the ledger entries
                Ledger::where('order_id', $order->id)->delete();
                
                // Recalculate balances for all remaining ledger entries for this customer
                if ($customerId) {
                    static::recalculateCustomerLedgerBalances($customerId);
                }
            }
        });
    }

    /**
     * Recalculate all ledger balances for a customer after deletion
     */
    protected static function recalculateCustomerLedgerBalances($customerId)
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            return;
        }

        // Get all ledger entries for this customer in chronological order
        $ledgers = Ledger::where('customer_id', $customerId)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Start with opening balance
        $runningBalance = (float) ($customer->opening_balance ?? 0);

        // Recalculate each entry's balance
        foreach ($ledgers as $ledger) {
            $runningBalance = $runningBalance - (float) $ledger->credit_amount + (float) $ledger->debit_amount;
            $ledger->balance = $runningBalance;
            $ledger->saveQuietly(); // Save without triggering events
        }
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
    public function getCustomerName()
    {
        if($this->customer) {
            return $this->customer->first_name . ' ' . $this->customer->last_name;
        }
        return __('customer.working');
    }

    public function total()
    {
        return $this->items->map(function ($i){
            return $i->price;
        })->sum();
    }

    public function formattedTotal()
    {
        return number_format($this->total(), 2);
    }

    public function receivedAmount()
    {
        return $this->payments->map(function ($i){
            return $i->amount;
        })->sum();
    }

    public function formattedReceivedAmount()
    {
        return number_format($this->receivedAmount(), 2);
    }
}
