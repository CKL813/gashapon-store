<?php

namespace App\Models;

use App\Enums\CouponType;
use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_uses',
        'used_count',
        'expires_at',
        'is_active',
        'is_welcome',
    ];

    protected $casts = [
        'type'             => CouponType::class,
        'value'            => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'expires_at'       => 'datetime',
        'is_active'        => 'boolean',
        'is_welcome'       => 'boolean',
    ];

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    // ── Business Logic ───────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->max_uses !== null && $this->used_count >= $this->max_uses;
    }

    /** Full validity check for storefront use. */
    public function isValid(string $orderSubtotal = '0'): bool
    {
        if (! $this->is_active || $this->isExpired() || $this->isExhausted()) {
            return false;
        }

        if ($this->min_order_amount !== null && $orderSubtotal < $this->min_order_amount) {
            return false;
        }

        return true;
    }

    /** Calculate the discount amount for a given subtotal. */
    public function calculateDiscount(string $subtotal): string
    {
        return match ($this->type) {
            CouponType::Fixed   => min($this->value, $subtotal),
            CouponType::Percent => round($subtotal * ($this->value / 100), 2),
        };
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
