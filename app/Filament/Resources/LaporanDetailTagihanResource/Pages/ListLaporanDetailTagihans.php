<?php

namespace App\Filament\Resources\LaporanDetailTagihanResource\Pages;

use App\Filament\Resources\LaporanDetailTagihanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporanDetailTagihans extends ListRecords
{
    protected static string $resource = LaporanDetailTagihanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
