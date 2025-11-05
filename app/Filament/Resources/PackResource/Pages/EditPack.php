<?php

namespace App\Filament\Resources\PackResource\Pages;

use App\Filament\Resources\PackResource;
use App\Models\Pack;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPack extends EditRecord
{
    protected static string $resource = PackResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Apply formatting to fields before saving
        $data['nama_pack'] = ucwords($data['nama_pack']);

        return $data;
    }

    protected function beforeSave(): void
    {
        // Get input values
        $id = $this->data['pack_id'] ?? null;
        $namaPack = $this->data['nama_pack'] ?? null;

        // Check if the same record exists
        $exists = Pack::where('nama_pack', ucwords($namaPack))
            ->where('pack_id', '!=', $id) // Exclude the current record
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Pack "'.ucwords($namaPack).'" sudah ada')
                ->danger()
                ->send();

            // Prevent form submission
            $this->halt();
        }
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

    public function getTitle(): string
    {
        return 'Ubah Pack';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data pack berhasil diperbarui.');
    }
}