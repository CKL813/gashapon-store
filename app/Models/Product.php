<?php

namespace App\Models;

use App\Enums\ProductType;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'brand_id',
        'category_id',
        'sku',
        'name',
        'slug',
        'description',
        'product_type',
        'price',
        'wholesale_price',
        'stock',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'product_type'    => ProductType::class,
        'price'           => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'is_active'       => 'boolean',
        'is_featured'     => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $product) {
            $product->slug ??= Str::slug($product->name);
            if (empty($product->sku)) {
                $product->sku = strtoupper(Str::random(8));
            }
        });
    }

    // ── Media Library ────────────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(800)
            ->height(800)
            ->nonQueued();
    }

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    public function scopeFeatured($query): void
    {
        $query->where('is_featured', true);
    }

    public function scopeSpecific($query): void
    {
        $query->where('product_type', ProductType::Specific);
    }

    public function scopeRandom($query): void
    {
        $query->where('product_type', ProductType::Random);
    }

    public function scopeInStock($query): void
    {
        $query->where('stock', '>', 0);
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    /** Returns the effective price for the given user (wholesale vs retail). */
    public function priceFor(?User $user): string
    {
        if ($user?->isWholesaleEligible() && $this->wholesale_price !== null) {
            return $this->wholesale_price;
        }

        return $this->price;
    }

    /** URL of the first image in the 'images' collection, or null. */
    public function getMainImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images', 'medium') ?: null;
    }

    public function isRandom(): bool
    {
        return $this->product_type === ProductType::Random;
    }

    public function isSpecific(): bool
    {
        return $this->product_type === ProductType::Specific;
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }
}
