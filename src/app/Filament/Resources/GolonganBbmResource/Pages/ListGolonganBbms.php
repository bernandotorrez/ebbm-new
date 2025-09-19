<?php

namespace App\Filament\Resources\GolonganBbmResource\Pages;

use App\Filament\Resources\GolonganBbmResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGolonganBbms extends ListRecords
{
    protected static string $resource = GolonganBbmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Golongan BBM'),
        ];
    }
}
