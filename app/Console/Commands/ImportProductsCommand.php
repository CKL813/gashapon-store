<?php

namespace App\Console\Commands;

use App\Enums\ProductType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Imports products from a CSV file and auto-downloads product images.
 *
 * CSV columns (header row required):
 *   sku, name, description, category, brand, product_type,
 *   price, wholesale_price, stock, image_urls
 *
 * image_urls: one or more URLs separated by "|"
 *
 * Example:
 *   php artisan products:import storage/app/imports/products.csv
 */
class ImportProductsCommand extends Command
{
    protected $signature = 'products:import
                            {file : Path to the CSV file}
                            {--fresh : Clear existing media before attaching new images}';

    protected $description = 'Import products from a CSV file with auto image download';

    /** Column index map built from the CSV header row. */
    private array $columns = [];

    public function handle(): int
    {
        $path = $this->argument('file');

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            $this->error("Cannot open file: {$path}");

            return self::FAILURE;
        }

        // Build column index map from header row
        $header = fgetcsv($handle);
        $this->columns = array_flip(array_map('trim', $header));

        $required = ['sku', 'name', 'price'];
        foreach ($required as $col) {
            if (! isset($this->columns[$col])) {
                $this->error("Missing required column: {$col}");
                fclose($handle);

                return self::FAILURE;
            }
        }

        $imported = 0;
        $skipped  = 0;
        $row      = 1;

        $this->info("Starting import from: {$path}");
        $this->newLine();

        while (($data = fgetcsv($handle)) !== false) {
            $row++;

            if (count($data) < count($this->columns)) {
                $this->warn("Row {$row}: skipping malformed row.");
                $skipped++;
                continue;
            }

            try {
                $this->importRow($data);
                $imported++;
                $this->line("  <info>✓</info> Row {$row}: {$this->col($data, 'name')}");
            } catch (\Throwable $e) {
                $skipped++;
                $this->warn("  Row {$row}: FAILED — {$e->getMessage()}");
            }
        }

        fclose($handle);

        $this->newLine();
        $this->info("Done. Imported: {$imported} | Skipped: {$skipped}");

        return self::SUCCESS;
    }

    private function importRow(array $data): void
    {
        $sku  = trim($this->col($data, 'sku'));
        $name = trim($this->col($data, 'name'));

        $brand    = $this->findOrCreateBrand($this->col($data, 'brand'));
        $category = $this->findOrCreateCategory($this->col($data, 'category'));

        $productType = ProductType::tryFrom($this->col($data, 'product_type') ?? '') ?? ProductType::Specific;

        $product = Product::withTrashed()->updateOrCreate(
            ['sku' => $sku],
            [
                'brand_id'        => $brand?->id,
                'category_id'     => $category?->id,
                'name'            => $name,
                'slug'            => Str::slug($name),
                'description'     => $this->col($data, 'description'),
                'product_type'    => $productType,
                'price'           => (float) ($this->col($data, 'price') ?? 0),
                'wholesale_price' => $this->col($data, 'wholesale_price')
                    ? (float) $this->col($data, 'wholesale_price')
                    : null,
                'stock'           => (int) ($this->col($data, 'stock') ?? 0),
                'is_active'       => true,
                'deleted_at'      => null, // restore if soft-deleted
            ]
        );

        $this->attachImages($product, $this->col($data, 'image_urls'));
    }

    private function attachImages(Product $product, ?string $imageUrls): void
    {
        if (empty($imageUrls)) {
            return;
        }

        $urls = array_filter(array_map('trim', explode('|', $imageUrls)));

        if (empty($urls)) {
            return;
        }

        if ($this->option('fresh')) {
            $product->clearMediaCollection('images');
        }

        foreach ($urls as $index => $url) {
            try {
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $filename  = Str::slug($product->name) . "-{$index}.{$extension}";

                $product->addMediaFromUrl($url)
                    ->usingFileName($filename)
                    ->toMediaCollection('images');
            } catch (\Throwable $e) {
                $this->warn("    Could not download image [{$url}]: {$e->getMessage()}");
            }
        }
    }

    private function findOrCreateBrand(?string $name): ?Brand
    {
        $name = trim($name ?? '');

        if ($name === '') {
            return null;
        }

        return Brand::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'is_active' => true]
        );
    }

    private function findOrCreateCategory(?string $name): ?Category
    {
        $name = trim($name ?? '');

        if ($name === '') {
            return null;
        }

        return Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'is_active' => true]
        );
    }

    /** Safely read a column by name; returns null if the column doesn't exist. */
    private function col(array $data, string $column): ?string
    {
        $index = $this->columns[$column] ?? null;

        if ($index === null) {
            return null;
        }

        $value = $data[$index] ?? '';

        return $value === '' ? null : $value;
    }
}
