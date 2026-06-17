<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingRateSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('shipping_rates')->truncate();

        $districts = [
            // Hong Kong Island
            ['district' => 'Central & Western',  'rate' => 30.00],
            ['district' => 'Wan Chai',            'rate' => 30.00],
            ['district' => 'Eastern',             'rate' => 30.00],
            ['district' => 'Southern',            'rate' => 35.00],
            // Kowloon
            ['district' => 'Yau Tsim Mong',      'rate' => 25.00],
            ['district' => 'Sham Shui Po',        'rate' => 25.00],
            ['district' => 'Kowloon City',        'rate' => 25.00],
            ['district' => 'Wong Tai Sin',        'rate' => 25.00],
            ['district' => 'Kwun Tong',           'rate' => 28.00],
            // New Territories
            ['district' => 'Kwai Tsing',          'rate' => 35.00],
            ['district' => 'Tsuen Wan',           'rate' => 35.00],
            ['district' => 'Tuen Mun',            'rate' => 40.00],
            ['district' => 'Yuen Long',           'rate' => 40.00],
            ['district' => 'North',               'rate' => 45.00],
            ['district' => 'Tai Po',              'rate' => 40.00],
            ['district' => 'Sha Tin',             'rate' => 35.00],
            ['district' => 'Sai Kung',            'rate' => 45.00],
            ['district' => 'Islands',             'rate' => 60.00],
        ];

        DB::table('shipping_rates')->insert(array_map(fn ($d) => [
            ...$d,
            'created_at' => now(),
            'updated_at' => now(),
        ], $districts));

        $this->command->info('✓ Seeded ' . count($districts) . ' HK shipping districts.');
    }
}
