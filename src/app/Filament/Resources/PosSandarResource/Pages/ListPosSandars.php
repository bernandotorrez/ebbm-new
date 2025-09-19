<?php

namespace App\Filament\Resources\PosSandarResource\Pages;

use App\Filament\Resources\PosSandarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPosSandars extends ListRecords
{
    protected static string $resource = PosSandarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pos Sandar'),
        ];
    }
}
