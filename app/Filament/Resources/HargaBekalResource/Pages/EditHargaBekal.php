<?php

namespace App\Filament\Resources\HargaBekalResource\Pages;

use App\Filament\Resources\HargaBekalResource;
use App\Models\HargaBekal;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditHargaBekal extends EditRecord
{
    protected static string $resource = HargaBekalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus'),
            Actions\ForceDeleteAction::make()
                ->label('Hapus Permanen'),
            Actions\RestoreAction::make()
                ->label('Pulihkan'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after editing
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Convert tanggal_update to date only (remove time)
        if (isset($data['tanggal_update'])) {
            $data['tanggal_update'] = \Carbon\Carbon::parse($data['tanggal_update'])->format('Y-m-d');
        }
        
        return $data;
    }

    protected function beforeSave(): void
    {
        // Get input values
        $id = $this->record->harga_bekal_id;
        $wilayahId = $this->data['wilayah_id'] ?? null;
        $bekalId = $this->data['bekal_id'] ?? null;
        $tanggalUpdate = $this->data['tanggal_update'] ?? null;

        // Validasi input tidak boleh null
        if (!$wilayahId || !$bekalId || !$tanggalUpdate) {
            Notification::make()
                ->title('Error!')
                ->body('Wilayah, Bekal, dan Tanggal Update harus diisi')
                ->danger()
                ->send();
            $this->halt();
        }

        // Convert to date only for comparison
        $tanggalUpdateDate = \Carbon\Carbon::parse($tanggalUpdate)->format('Y-m-d');

        // Check if the same record exists (Wilayah + Bekal + Tanggal Update, excluding current record)
        $exists = HargaBekal::where('wilayah_id', $wilayahId)
            ->where('bekal_id', $bekalId)
            ->whereDate('tanggal_update', $tanggalUpdateDate)
            ->where('harga_bekal_id', '!=', $id)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Harga BBM untuk kombinasi Wilayah, Bekal, dan Tanggal Update yang sama sudah ada')
                ->danger()
                ->send();

            // Prevent form submission
            $this->halt();
        }
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data Harga BBM berhasil diperbarui.');
    }

    public function getFormActions(): array
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
        return 'Ubah Harga BBM';
    }
}
