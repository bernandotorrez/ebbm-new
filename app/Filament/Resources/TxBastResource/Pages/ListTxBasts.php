<?php

namespace App\Filament\Resources\TxBastResource\Pages;

use App\Filament\Resources\TxBastResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTxBasts extends ListRecords
{
    protected static string $resource = TxBastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
