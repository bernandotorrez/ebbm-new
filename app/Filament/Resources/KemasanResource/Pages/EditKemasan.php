<?php

namespace App\Filament\Resources\KemasanResource\Pages;

use App\Filament\Resources\KemasanResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditKemasan extends EditRecord
{
    protected static string $resource = KemasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan'),
            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data kemasan berhasil diperbarui.');
    }
    
    public function getTitle(): string
    {
        return 'Ubah Kemasan';
    }
}