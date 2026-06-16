<?php

namespace App\Models;

use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price',
        'wholesale_price',
        'stock',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    /** Effective retail price: variant override or fall back to parent product. */
    public function getEffectivePriceAttribute(): string
    {
        return $this->price ?? $this->product->price;
    }

    /** Effective wholesale price, falling back to parent product. */
    public function getEffectiveWholesalePriceAttribute(): ?string
    {
        return $this->wholesale_price ?? $this->product->wholesale_price;
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
