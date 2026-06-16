<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $revenue = Order::whereIn('status', [
            OrderStatus::Delivered->value,
            OrderStatus::Shipped->value,
        ])->sum('total');

        return [
            Stat::make('Active Products', Product::active()->count())
                ->description('Total in catalog')
                ->icon('heroicon-o-cube')
                ->color('info'),

            Stat::make('Total Orders', Order::count())
                ->description('All time')
                ->icon('heroicon-o-shopping-cart')
                ->color('success'),

            Stat::make('Revenue', '$' . number_format($revenue, 2))
                ->description('Shipped & delivered')
                ->icon('heroicon-o-banknotes')
                ->color('warning'),

            Stat::make('Pending Orders', Order::where('status', OrderStatus::Pending->value)->count())
                ->description('Need attention')
                ->icon('heroicon-o-clock')
                ->color('danger'),
        ];
    }
}
