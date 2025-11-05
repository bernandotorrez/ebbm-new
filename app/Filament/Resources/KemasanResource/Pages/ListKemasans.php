<?php

namespace App\Filament\Resources\KemasanResource\Pages;

use App\Filament\Resources\KemasanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKemasans extends ListRecords
{
    protected static string $resource = KemasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Kemasan'),
        ];
    }
}