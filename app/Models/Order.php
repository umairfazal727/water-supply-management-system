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
                    'description' => "Order #{$order->id} - {$order->product_type} delivery - {$order->tanker_size} tanker",
                    'transaction_type' => 'order'
                ]);
            }
        });
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
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
