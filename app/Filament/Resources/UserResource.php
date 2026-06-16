<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static \BackedEnum|string|null $navigationIcon  = 'heroicon-o-users';
    protected static \UnitEnum|string|null   $navigationGroup = 'Users';
    protected static ?int                    $navigationSort  = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Account')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context) => $context === 'create')
                    ->label('Password')
                    ->helperText('Leave blank to keep current password when editing'),

                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(30),
            ])->columns(2),

            Schemas\Components\Section::make('B2B / Wholesale')->schema([
                Forms\Components\Toggle::make('is_b2b')
                    ->label('B2B Account')
                    ->helperText('Marks this user as a wholesale buyer.')
                    ->reactive(),

                Forms\Components\Toggle::make('is_approved')
                    ->label('Approved for Wholesale')
                    ->helperText('Must be approved before wholesale prices are applied.')
                    ->visible(fn (Schemas\Components\Utilities\Get $get) => (bool) $get('is_b2b')),

                Forms\Components\TextInput::make('company_name')
                    ->maxLength(255)
                    ->visible(fn (Schemas\Components\Utilities\Get $get) => (bool) $get('is_b2b')),
            ])->columns(2),

            Schemas\Components\Section::make('Roles')->schema([
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('company_name')
                    ->label('Company')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_b2b')
                    ->boolean()
                    ->label('B2B'),

                Tables\Columns\ToggleColumn::make('is_approved')
                    ->label('Approved'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->label('Roles'),

                Tables\Columns\TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Orders'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_b2b')->label('B2B Only'),
                Tables\Filters\TernaryFilter::make('is_approved')->label('Approved'),
            ])
            ->actions([
                Actions\EditAction::make(),
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
