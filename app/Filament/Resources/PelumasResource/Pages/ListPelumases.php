<?php

namespace App\Filament\Resources\PelumasResource\Pages;

use App\Filament\Resources\PelumasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPelumases extends ListRecords
{
    protected static string $resource = PelumasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pelumas'),
        ];
    }
}