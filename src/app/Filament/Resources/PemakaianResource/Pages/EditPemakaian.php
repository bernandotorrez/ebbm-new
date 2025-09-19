<?php

namespace App\Filament\Resources\PemakaianResource\Pages;

use App\Filament\Resources\PemakaianResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPemakaian extends EditRecord
{
    protected static string $resource = PemakaianResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
