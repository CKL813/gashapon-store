<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'coupon_id',
        'status',
        'shipping_address',
        'billing_address',
        'subtotal',
        'shipping_cost',
        'discount',
        'total',
        'guest_name',
        'guest_email',
        'notes',
        'stripe_payment_intent_id',
        'stripe_payment_status',
    ];

    protected $casts = [
        'status'           => OrderStatus::class,
        'shipping_address' => 'array',
        'billing_address'  => 'array',
        'subtotal'         => 'decimal:2',
        'shipping_cost'    => 'decimal:2',
        'discount'         => 'decimal:2',
        'total'            => 'decimal:2',
    ];

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query): void
    {
        $query->whereIn('status', OrderStatus::active());
    }

    public function scopeForStatus($query, OrderStatus $status): void
    {
        $query->where('status', $status);
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    /** Display name, from user account or guest name. */
    public function getCustomerNameAttribute(): string
    {
        return $this->user?->name ?? $this->guest_name ?? 'Guest';
    }

    public function getCustomerEmailAttribute(): string
    {
        return $this->user?->email ?? $this->guest_email ?? '';
    }

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
