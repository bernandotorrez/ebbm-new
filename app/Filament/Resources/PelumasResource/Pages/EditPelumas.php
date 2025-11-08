<?php

namespace App\Filament\Resources\PelumasResource\Pages;

use App\Filament\Resources\PelumasResource;
use App\Models\Pelumas;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPelumas extends EditRecord
{
    protected static string $resource = PelumasResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    protected function beforeSave(): void
    {
        // Get input values
        $id = $this->data['pelumas_id'] ?? null;
        $namaPelumas = $this->data['nama_pelumas'] ?? null;
        $packId = $this->data['pack_id'] ?? null;
        $kemasanId = $this->data['kemasan_id'] ?? null;
        $tahun = $this->data['tahun'] ?? null;

        // Check if the same record exists
        $exists = Pelumas::where('nama_pelumas', $namaPelumas)
            ->where('pack_id', $packId)
            ->where('kemasan_id', $kemasanId)
            ->where('tahun', $tahun)
            ->where('pelumas_id', '!=', $id) // Exclude the current record
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Pelumas "'.$namaPelumas.'" dengan pack, kemasan, dan tahun yang sama sudah ada')
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
            ->body('Data pelumas berhasil diperbarui.');
    }
    
    public function getTitle(): string
    {
        return 'Ubah Pelumas';
    }
}