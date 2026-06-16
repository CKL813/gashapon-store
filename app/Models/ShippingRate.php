<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = ['district', 'rate'];

    protected $casts = ['rate' => 'decimal:2'];

    /** Look up the shipping rate for a given district name (case-insensitive). */
    public static function forDistrict(string $district): ?self
    {
        return static::whereRaw('LOWER(district) = ?', [strtolower($district)])->first();
    }

    /** Returns the rate value, or 0.00 if no rate is configured. */
    public static function rateForDistrict(string $district): string
    {
        return static::forDistrict($district)?->rate ?? '0.00';
    }
}
