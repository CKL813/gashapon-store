<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-shopping-cart';
    protected static \UnitEnum|string|null   $navigationGroup = 'Commerce';
    protected static ?int                    $navigationSort  = 1;

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Infolists\Components\Section::make('Order Summary')->schema([
                Infolists\Components\TextEntry::make('id')
                    ->label('Order #'),

                Infolists\Components\TextEntry::make('status')
                    ->badge()
                    ->color(fn (OrderStatus $state) => match ($state) {
                        OrderStatus::Pending    => 'warning',
                        OrderStatus::Processing => 'info',
                        OrderStatus::Shipped    => 'primary',
                        OrderStatus::Delivered  => 'success',
                        OrderStatus::Cancelled  => 'danger',
                        OrderStatus::Refunded   => 'gray',
                    }),

                Infolists\Components\TextEntry::make('customer_name')
                    ->label('Customer'),

                Infolists\Components\TextEntry::make('customer_email')
                    ->label('Email'),

                Infolists\Components\TextEntry::make('subtotal')->money('USD'),
                Infolists\Components\TextEntry::make('shipping_cost')->money('USD'),
                Infolists\Components\TextEntry::make('discount')->money('USD'),
                Infolists\Components\TextEntry::make('total')->money('USD')->weight('bold'),

                Infolists\Components\TextEntry::make('created_at')->dateTime()->label('Placed At'),
                Infolists\Components\TextEntry::make('notes')->placeholder('—'),
            ])->columns(2),

            Infolists\Components\Section::make('Shipping Address')->schema([
                Infolists\Components\TextEntry::make('shipping_address')
                    ->formatStateUsing(fn ($state) => collect($state)
                        ->filter()
                        ->implode(', ')
                    )
                    ->label('Address'),
            ]),

            Infolists\Components\Section::make('Items')->schema([
                Infolists\Components\RepeatableEntry::make('items')->schema([
                    Infolists\Components\TextEntry::make('product_name')->label('Product'),
                    Infolists\Components\TextEntry::make('variant.name')->label('Variant')->placeholder('—'),
                    Infolists\Components\TextEntry::make('quantity'),
                    Infolists\Components\TextEntry::make('unit_price')->money('USD'),
                    Infolists\Components\TextEntry::make('total_price')->money('USD'),
                ])->columns(5),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order #')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->getStateUsing(fn (Order $record) => $record->customer_name)
                    ->searchable(query: fn ($query, $search) => $query
                        ->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhere('guest_name', 'like', "%{$search}%")
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => OrderStatus::Pending->value,
                        'info'    => OrderStatus::Processing->value,
                        'primary' => OrderStatus::Shipped->value,
                        'success' => OrderStatus::Delivered->value,
                        'danger'  => OrderStatus::Cancelled->value,
                        'gray'    => OrderStatus::Refunded->value,
                    ]),

                Tables\Columns\TextColumn::make('total')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Placed'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(OrderStatus::class),
            ])
            ->actions([
                Actions\ViewAction::make(),

                Actions\Action::make('updateStatus')
                    ->label('Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options(OrderStatus::class)
                            ->required(),
                    ])
                    ->fillForm(fn (Order $record) => ['status' => $record->status])
                    ->action(function (Order $record, array $data): void {
                        $record->update(['status' => $data['status']]);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
