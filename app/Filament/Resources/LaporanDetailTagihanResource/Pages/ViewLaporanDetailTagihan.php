<?php

namespace App\Filament\Resources\LaporanDetailTagihanResource\Pages;

use App\Filament\Resources\LaporanDetailTagihanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLaporanDetailTagihan extends ViewRecord
{
    protected static string $resource = LaporanDetailTagihanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
