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
        $user = auth()->user();
        
        // Hide tombol Create untuk Kanpus
        if ($user && $user->level->value === \App\Enums\LevelUser::KANPUS->value) {
            return [];
        }
        
        return [
            Actions\CreateAction::make(),
        ];
    }
}
