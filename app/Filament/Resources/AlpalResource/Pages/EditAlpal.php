<?php

namespace App\Filament\Resources\AlpalResource\Pages;

use App\Filament\Resources\AlpalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlpal extends EditRecord
{
    protected static string $resource = AlpalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
