<?php

namespace App\Filament\Resources\PackResource\Pages;

use App\Filament\Resources\PackResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePack extends CreateRecord
{
    protected static string $resource = PackResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Buat'),
            $this->getCreateAnotherFormAction()
                ->label('Buat & Buat lainnya'),
            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'Buat Pack';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->url(static::getResource()::getUrl('index')),
        ];
    }
}
