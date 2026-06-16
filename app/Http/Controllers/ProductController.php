<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $products = Product::active()
            ->when($request->category, fn ($q) => $q->whereHas(
                'category', fn ($q2) => $q2->where('slug', $request->category)
            ))
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->type, fn ($q) => $q->where('product_type', $request->type))
            ->when($request->min_price, fn ($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->max_price, fn ($q) => $q->where('price', '<=', $request->max_price))
            ->when($request->sort === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when($request->sort === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->when(! $request->sort, fn ($q) => $q->orderByDesc('created_at'))
            ->with(['media', 'category'])
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Product $p) => [
                'id'           => $p->id,
                'name'         => $p->name,
                'slug'         => $p->slug,
                'price'        => (float) $p->priceFor(auth()->user()),
                'product_type' => $p->product_type->value,
                'stock'        => $p->stock,
                'image_url'    => $p->getFirstMediaUrl('images', 'medium') ?: null,
                'category'     => $p->category?->name,
            ]);

        return Inertia::render('Products/Index', [
            'products'   => $products,
            'categories' => Category::active()->roots()->orderBy('sort_order')->get(['id', 'name', 'slug']),
            'filters'    => $request->only(['category', 'search', 'type', 'min_price', 'max_price', 'sort']),
        ]);
    }

    public function show(string $slug): Response
    {
        $product = Product::active()
            ->where('slug', $slug)
            ->with(['media', 'category', 'brand', 'activeVariants'])
            ->firstOrFail();

        return Inertia::render('Products/Show', [
            'product' => [
                'id'               => $product->id,
                'name'             => $product->name,
                'slug'             => $product->slug,
                'sku'              => $product->sku,
                'description'      => $product->description,
                'product_type'     => $product->product_type->value,
                'price'            => (float) $product->priceFor(auth()->user()),
                'wholesale_price'  => $product->wholesale_price ? (float) $product->wholesale_price : null,
                'stock'            => $product->stock,
                'is_featured'      => $product->is_featured,
                'meta_title'       => $product->meta_title,
                'meta_description' => $product->meta_description,
                'category'         => $product->category
                    ? ['name' => $product->category->name, 'slug' => $product->category->slug]
                    : null,
                'brand'   => $product->brand ? ['name' => $product->brand->name] : null,
                'images'  => $product->getMedia('images')->map(fn ($m) => [
                    'id'           => $m->id,
                    'url'          => $m->getUrl('medium'),
                    'thumb_url'    => $m->getUrl('thumb'),
                    'original_url' => $m->getUrl(),
                ]),
                'variants' => $product->activeVariants->map(fn ($v) => [
                    'id'    => $v->id,
                    'name'  => $v->name,
                    'sku'   => $v->sku,
                    'price' => $v->price ? (float) $v->price : null,
                    'stock' => $v->stock,
                    'is_active' => $v->is_active,
                ]),
            ],
        ]);
    }
}
