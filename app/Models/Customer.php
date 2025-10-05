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
}
