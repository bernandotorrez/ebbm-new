<?php

namespace App\Filament\Resources\WilayahResource\Pages;

use App\Filament\Resources\WilayahResource;
use App\Models\Wilayah;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateWilayah extends CreateRecord
{
    protected static string $resource = WilayahResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        // Get input values
        $wilayah_ke = $this->data['wilayah_ke'] ?? null;

        // Check if the same record exists
        $exists = Wilayah::where('wilayah_ke', $wilayah_ke)
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Wilayah ke "'.$wilayah_ke.'" sudah ada')
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
            ->body('Data wilayah berhasil ditambahkan.');
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