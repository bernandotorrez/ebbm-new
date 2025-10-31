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

    protected function beforeSave(): void
    {
        // Get input values
        $id = $this->data['harga_bekal_id'] ?? null;
        $kotaId = $this->data['kota_id'] ?? null;
        $bekalId = $this->data['bekal_id'] ?? null;

        // Check if the same record exists (excluding current record)
        $exists = HargaBekal::where('kota_id', $kotaId)
            ->where('bekal_id', $bekalId)
            ->where('harga_bekal_id', '!=', $id)
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Harga bekal untuk kota dan bekal yang sama sudah ada')
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
            ->body('Data harga bekal berhasil diperbarui.');
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
        return 'Ubah Harga Bekal';
    }
}
