<?php

namespace App\Filament\Resources\KemasanResource\Pages;

use App\Filament\Resources\KemasanResource;
use App\Models\Kemasan;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditKemasan extends EditRecord
{
    protected static string $resource = KemasanResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    protected function beforeSave(): void
    {
        // Get input values
        $id = $this->data['kemasan_id'] ?? null;
        $packId = $this->data['pack_id'] ?? null;
        $kemasanLiter = $this->data['kemasan_liter'] ?? null;

        // Check if the same record exists (kombinasi pack_id dan kemasan_liter)
        $exists = Kemasan::where('pack_id', $packId)
            ->where('kemasan_liter', $kemasanLiter)
            ->where('kemasan_id', '!=', $id) // Exclude the current record
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