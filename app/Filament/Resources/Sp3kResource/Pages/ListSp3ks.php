<?php

namespace App\Filament\Resources\Sp3kResource\Pages;

use App\Filament\Resources\Sp3kResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSp3ks extends ListRecords
{
    protected static string $resource = Sp3kResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah SP3K'),
        ];
    }
}