<?php

namespace App\Filament\Resources;

use App\Enums\ProductType;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-cube';
    protected static \UnitEnum|string|null   $navigationGroup = 'Catalog';
    protected static ?int                    $navigationSort  = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            // ── Left column (2/3 width) ──────────────────────────────────────
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Basic Info')->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('sku')
                        ->label('SKU')
                        ->unique(ignoreRecord: true)
                        ->maxLength(100)
                        ->placeholder('Auto-generated if left blank'),

                    Forms\Components\Select::make('brand_id')
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                        ]),

                    Forms\Components\RichEditor::make('description')
                        ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link'])
                        ->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Section::make('Product Type & Pricing')->schema([
                    Forms\Components\ToggleButtons::make('product_type')
                        ->label('Product Type')
                        ->options([
                            'specific' => 'Specific Item',
                            'random'   => 'Random Capsule',
                        ])
                        ->icons([
                            'specific' => 'heroicon-o-cube',
                            'random'   => 'heroicon-o-gift',
                        ])
                        ->colors([
                            'specific' => 'info',
                            'random'   => 'warning',
                        ])
                        ->inline()
                        ->default('specific')
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0),

                    Forms\Components\TextInput::make('wholesale_price')
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0)
                        ->label('Wholesale Price (B2B)')
                        ->helperText('Leave blank if not available for B2B'),

                    Forms\Components\TextInput::make('stock')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0),
                ])->columns(2),

                Forms\Components\Section::make('Variants')
                    ->description('Add size/color options. Leave empty if product has no variants.')
                    ->schema([
                        Forms\Components\Repeater::make('variants')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->placeholder('e.g. Red / L')
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('sku')
                                    ->label('Variant SKU')
                                    ->unique('product_variants', 'sku', ignoreRecord: true)
                                    ->placeholder('Auto if blank'),

                                Forms\Components\TextInput::make('stock')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),

                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->placeholder('Inherit from product')
                                    ->minValue(0),

                                Forms\Components\TextInput::make('wholesale_price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->placeholder('Inherit from product')
                                    ->minValue(0),

                                Forms\Components\TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0),

                                Forms\Components\Toggle::make('is_active')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->columns(4)
                            ->reorderable('sort_order')
                            ->collapsible()
                            ->itemLabel(fn (array $state) => $state['name'] ?? 'New Variant')
                            ->addActionLabel('Add Variant'),
                    ])->collapsible(),

                Forms\Components\Section::make('SEO')->schema([
                    Forms\Components\TextInput::make('meta_title')
                        ->maxLength(255)
                        ->placeholder('Defaults to product name'),

                    Forms\Components\Textarea::make('meta_description')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2)->collapsible()->collapsed(),

            ])->columnSpan(2),

            // ── Right column (1/3 width) ─────────────────────────────────────
            Forms\Components\Group::make()->schema([

                Forms\Components\Section::make('Status')->schema([
                    Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                        ]),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

                    Forms\Components\Toggle::make('is_featured')
                        ->label('Featured'),
                ]),

                Forms\Components\Section::make('Images')->schema([
                    Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                        ->collection('images')
                        ->multiple()
                        ->reorderable()
                        ->maxFiles(10)
                        ->image()
                        ->imageEditor()
                        ->label(''),
                ]),

            ])->columnSpan(1),

        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')
                    ->collection('images')
                    ->conversion('thumb')
                    ->label('')
                    ->width(50)
                    ->height(50),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record) => $record->sku),

                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\BadgeColumn::make('product_type')
                    ->label('Type')
                    ->colors([
                        'info'    => 'specific',
                        'warning' => 'random',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'random' ? 'Random Capsule' : 'Specific'),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->color(fn (Product $record) => $record->stock === 0 ? 'danger' : null),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\ToggleColumn::make('is_featured')
                    ->label('Featured'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('product_type')
                    ->options([
                        'specific' => 'Specific',
                        'random'   => 'Random Capsule',
                    ])
                    ->label('Type'),

                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured'),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Out of Stock')
                    ->query(fn ($query) => $query->where('stock', 0)),
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
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
