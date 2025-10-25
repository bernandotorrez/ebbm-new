<?php

namespace App\Filament\Resources\PemakaianResource\Pages;

use App\Filament\Resources\PemakaianResource;
use App\Models\Pemakaian;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePemakaian extends CreateRecord
{
    protected static string $resource = PemakaianResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data pemakaian berhasil ditambahkan.');
    }

    public function getFormActions(): array
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
}
