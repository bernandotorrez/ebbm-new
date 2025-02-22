<?php

namespace App\Filament\Resources\BekalResource\Pages;

use App\Filament\Resources\BekalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBekal extends EditRecord
{
    protected static string $resource = BekalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
