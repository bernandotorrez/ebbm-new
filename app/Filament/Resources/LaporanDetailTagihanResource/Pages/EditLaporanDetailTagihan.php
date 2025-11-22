<?php

namespace App\Filament\Resources\LaporanDetailTagihanResource\Pages;

use App\Filament\Resources\LaporanDetailTagihanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanDetailTagihan extends EditRecord
{
    protected static string $resource = LaporanDetailTagihanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
