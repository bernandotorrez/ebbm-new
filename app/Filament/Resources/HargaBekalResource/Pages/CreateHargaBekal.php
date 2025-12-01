<?php

namespace App\Filament\Resources\HargaBekalResource\Pages;

use App\Filament\Resources\HargaBekalResource;
use App\Models\HargaBekal;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateHargaBekal extends CreateRecord
{
    protected static string $resource = HargaBekalResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Convert tanggal_update to date only (remove time)
        if (isset($data['tanggal_update'])) {
            $data['tanggal_update'] = \Carbon\Carbon::parse($data['tanggal_update'])->format('Y-m-d');
        }
        
        return $data;
    }

    protected function beforeCreate(): void
    {
        // Get input values
        $kotaId = $this->data['kota_id'] ?? null;
        $bekalId = $this->data['bekal_id'] ?? null;
        $tanggalUpdate = $this->data['tanggal_update'] ?? null;

        // Validasi input tidak boleh null
        if (!$kotaId || !$bekalId || !$tanggalUpdate) {
            Notification::make()
                ->title('Error!')
                ->body('Kota, Bekal, dan Tanggal Update harus diisi')
                ->danger()
                ->send();
            $this->halt();
        }

        // Convert to date only for comparison
        $tanggalUpdateDate = \Carbon\Carbon::parse($tanggalUpdate)->format('Y-m-d');

        // Check if the same record exists (Kota + Bekal + Tanggal Update)
        $exists = HargaBekal::where('kota_id', $kotaId)
            ->where('bekal_id', $bekalId)
            ->whereDate('tanggal_update', $tanggalUpdateDate)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Harga BBM untuk kombinasi Kota, Bekal, dan Tanggal Update yang sama sudah ada')
                ->danger()
                ->send();

            // Prevent form submission
            $this->halt();
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data Harga BBM berhasil ditambahkan.');
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
        return 'Buat Harga BBM';
    }
}
