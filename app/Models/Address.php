<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address_line_1',
        'address_line_2',
        'district',
        'city',
        'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    protected static function boot(): void
    {
        parent::boot();

        // When an address is set as default, unset all others for the same user.
        static::saving(function (self $address) {
            if ($address->is_default) {
                static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id ?? 0)
                    ->update(['is_default' => false]);
            }
        });
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function toShippingArray(): array
    {
        return [
            'name'           => $this->name,
            'phone'          => $this->phone,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'district'       => $this->district,
            'city'           => $this->city,
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
