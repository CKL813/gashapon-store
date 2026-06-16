<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function home(): Response
    {
        $featured = Product::active()
            ->featured()
            ->with('media', 'category')
            ->limit(8)
            ->get()
            ->map(fn (Product $p) => $this->productCard($p));

        $categories = Category::active()
            ->roots()
            ->withCount('products')
            ->orderBy('sort_order')
            ->limit(6)
            ->get(['id', 'name', 'slug', 'description']);

        return Inertia::render('Home', compact('featured', 'categories'));
    }

    private function productCard(Product $p): array
    {
        return [
            'id'           => $p->id,
            'name'         => $p->name,
            'slug'         => $p->slug,
            'price'        => (float) $p->priceFor(auth()->user()),
            'product_type' => $p->product_type->value,
            'stock'        => $p->stock,
            'image_url'    => $p->getFirstMediaUrl('images', 'medium') ?: null,
            'category'     => $p->category?->name,
        ];
    }
}
