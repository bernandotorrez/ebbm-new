<?php

namespace App\Filament\Resources\Sp3mResource\Pages;

use App\Filament\Resources\Sp3mResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSp3ms extends ListRecords
{
    protected static string $resource = Sp3mResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah SP3M'),
        ];
    }
}
