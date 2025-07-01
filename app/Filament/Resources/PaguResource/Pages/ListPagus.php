<?php

namespace App\Filament\Resources\PaguResource\Pages;

use App\Filament\Resources\PaguResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPagus extends ListRecords
{
    protected static string $resource = PaguResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
