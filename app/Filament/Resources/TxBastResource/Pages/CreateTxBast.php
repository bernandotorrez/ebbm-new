<?php

namespace App\Filament\Resources\TxBastResource\Pages;

use App\Filament\Resources\TxBastResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTxBast extends CreateRecord
{
    protected static string $resource = TxBastResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
