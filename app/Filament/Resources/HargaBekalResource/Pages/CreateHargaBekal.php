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

    protected function beforeCreate(): void
    {
        // Get input values
        $kotaId = $this->data['kota_id'] ?? null;
        $bekalId = $this->data['bekal_id'] ?? null;

        // Check if the same record exists
        $exists = HargaBekal::where('kota_id', $kotaId)
            ->where('bekal_id', $bekalId)
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Harga BBM untuk kota dan bekal yang sama sudah ada')
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
