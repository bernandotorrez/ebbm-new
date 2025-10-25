<?php

namespace App\Filament\Resources\HargaBekalResource\Pages;

use App\Filament\Resources\HargaBekalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHargaBekals extends ListRecords
{
    protected static string $resource = HargaBekalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Harga Bekal'),
        ];
    }
}
