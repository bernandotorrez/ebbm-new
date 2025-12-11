<?php

namespace App\Filament\Resources\KemasanResource\Pages;

use App\Filament\Resources\KemasanResource;
use App\Models\Kemasan;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateKemasan extends CreateRecord
{
    protected static string $resource = KemasanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function beforeCreate(): void
    {
        // Get input values
        $packId = $this->data['pack_id'] ?? null;
        $kemasanLiter = $this->data['kemasan_liter'] ?? null;

        // Check if the same record exists (kombinasi pack_id dan kemasan_liter)
        $exists = Kemasan::where('pack_id', $packId)
            ->where('kemasan_liter', $kemasanLiter)
            ->exists();

        if ($exists) {
            // Get pack name for better error message
            $packName = $packId ? \App\Models\Pack::find($packId)?->nama_pack : 'Unknown';
            
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Kemasan dengan Pack "'.$packName.'" dan liter "'.$kemasanLiter.'" sudah ada')
                ->danger()
                ->send();

            // Prevent form submission
            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data kemasan berhasil ditambahkan.');
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
    
    public function getTitle(): string
    {
        return 'Buat Kemasan';
    }
}