<?php

namespace App\Filament\Resources\KantorSarResource\Pages;

use App\Filament\Resources\KantorSarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKantorSars extends ListRecords
{
    protected static string $resource = KantorSarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
