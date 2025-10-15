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
        'is_active'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}
