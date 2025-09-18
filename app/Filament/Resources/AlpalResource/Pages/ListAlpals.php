<?php

namespace App\Filament\Resources\AlpalResource\Pages;

use App\Filament\Resources\AlpalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlpals extends ListRecords
{
    protected static string $resource = AlpalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Alpal'),
        ];
    }
}
