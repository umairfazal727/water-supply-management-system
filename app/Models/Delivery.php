<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'customer_id',
        'delivery_customer_id',
        'order_id',
        'delivery_number',
        'delivery_date',
        'delivery_time',
        'customer_site',
        'customer_location',
        'trip_size',
        'total_amount',
        'payment_method',
        'status',
        'notes',
        'delivery_photos',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'delivery_time' => 'datetime:H:i',
        'total_amount' => 'decimal:2',
        'delivery_photos' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($delivery) {
            // Create ledger entry for credit deliveries
            if ($delivery->delivery_customer_id && in_array($delivery->payment_method, ['credit', 'check'])) {
                DeliveryLedger::createEntry([
                    'delivery_customer_id' => $delivery->delivery_customer_id,
                    'delivery_id' => $delivery->id,
                    'transaction_date' => $delivery->delivery_date ?? now(),
                    'entry_origin' => 'DELIVERY-' . $delivery->id,
                    'debit_amount' => 0,
                    'credit_amount' => $delivery->total_amount,
                    'description' => "Delivery #{$delivery->delivery_number} - {$delivery->trip_size} gallons",
                    'transaction_type' => 'delivery'
                ]);
            }
        });
    }

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryCustomer(): BelongsTo
    {
        return $this->belongsTo(DeliveryCustomer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('delivery_date', [$startDate, $endDate]);
    }

    public function scopeByDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeByVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }
}
