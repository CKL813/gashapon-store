<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importCsv')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('CSV File')
                        ->disk('local')
                        ->directory('imports/csv')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                        ->required()
                        ->helperText('Columns: sku, name, description, category, brand, product_type, price, wholesale_price, stock, image_urls'),

                    Forms\Components\Toggle::make('fresh')
                        ->label('Clear existing images before import')
                        ->default(false),
                ])
                ->action(function (array $data): void {
                    $storedPath = $data['file'];
                    $fullPath   = Storage::disk('local')->path($storedPath);

                    Artisan::call('products:import', [
                        'file'    => $fullPath,
                        '--fresh' => (bool) ($data['fresh'] ?? false),
                    ]);

                    $output = Artisan::output();

                    Notification::make()
                        ->title('Import Complete')
                        ->body(strip_tags($output))
                        ->success()
                        ->send();

                    Storage::disk('local')->delete($storedPath);
                }),

            Actions\CreateAction::make(),
        ];
    }
}
