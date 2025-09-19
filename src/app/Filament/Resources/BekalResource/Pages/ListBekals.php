<?php

namespace App\Filament\Resources\BekalResource\Pages;

use App\Filament\Resources\BekalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBekals extends ListRecords
{
    protected static string $resource = BekalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Bekal'),
        ];
    }
}
