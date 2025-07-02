<?php

namespace App\Filament\Resources\Sp3mResource\Pages;

use App\Filament\Resources\Sp3mResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSp3m extends EditRecord
{
    protected static string $resource = Sp3mResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
