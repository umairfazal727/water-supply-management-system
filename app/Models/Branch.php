<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'is_active'
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
