<?php

namespace App\Filament\Resources;

use App\Enums\CouponType;
use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-ticket';
    protected static \UnitEnum|string|null   $navigationGroup = 'Commerce';
    protected static ?int                    $navigationSort  = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Coupon Details')->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->helperText('e.g. WELCOME10, SUMMER20')
                    ->afterStateUpdated(fn (Schemas\Components\Utilities\Set $set, ?string $state) => $set('code', strtoupper($state ?? '')))
                    ->live(onBlur: true),

                Forms\Components\ToggleButtons::make('type')
                    ->options(CouponType::class)
                    ->required()
                    ->inline()
                    ->default(CouponType::Fixed),

                Forms\Components\TextInput::make('value')
                    ->required()
                    ->numeric()
                    ->prefix(fn (Schemas\Components\Utilities\Get $get) => $get('type') === 'percent' ? '%' : '$')
                    ->minValue(0),

                Forms\Components\TextInput::make('min_order_amount')
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0)
                    ->label('Minimum Order Amount')
                    ->placeholder('No minimum'),

                Forms\Components\TextInput::make('max_uses')
                    ->numeric()
                    ->minValue(1)
                    ->label('Max Uses')
                    ->placeholder('Unlimited'),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expires At')
                    ->placeholder('Never'),
            ])->columns(2),

            Schemas\Components\Section::make('Settings')->schema([
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Forms\Components\Toggle::make('is_welcome')
                    ->label('Welcome Coupon')
                    ->helperText('Auto-applied as a $10 discount for new registrations. Only one should be active at a time.')
                    ->reactive(),
            ])->columns(2),

            Schemas\Components\Section::make('Usage')->schema([
                Forms\Components\TextInput::make('used_count')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->label('Times Used'),
            ])->visibleOn('edit'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'info'    => 'fixed',
                        'success' => 'percent',
                    ]),

                Tables\Columns\TextColumn::make('value')
                    ->formatStateUsing(fn (Coupon $record) => $record->type === CouponType::Percent
                        ? $record->value . '%'
                        : '$' . number_format($record->value, 2)
                    ),

                Tables\Columns\TextColumn::make('used_count')
                    ->label('Used')
                    ->formatStateUsing(fn (Coupon $record) => $record->max_uses
                        ? "{$record->used_count} / {$record->max_uses}"
                        : $record->used_count
                    ),

                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),

                Tables\Columns\IconColumn::make('is_welcome')
                    ->boolean()
                    ->label('Welcome'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                Tables\Filters\TernaryFilter::make('is_welcome')->label('Welcome Only'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
