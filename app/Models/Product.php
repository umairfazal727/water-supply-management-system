<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'barcode',
        'price',
        'tax',
        'quantity',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function getImageUrl()
    {
        if ($this->image) {
            /** @phpstan-ignore-next-line */
            return Storage::disk('public_uploads')->url($this->image);
        }
        return asset('img/img-placeholder.jpg');
    }

}
