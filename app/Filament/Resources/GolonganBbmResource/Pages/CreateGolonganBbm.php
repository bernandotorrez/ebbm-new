<?php

namespace App\Filament\Resources\GolonganBbmResource\Pages;

use App\Filament\Resources\GolonganBbmResource;
use App\Models\GolonganBbm;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateGolonganBbm extends CreateRecord
{
    protected static string $resource = GolonganBbmResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Apply ucwords() to the 'bekal' field before saving
        $data['golongan'] = ucwords($data['golongan']);

        return $data;
    }

    protected function beforeCreate(): void
    {
        // Get input values
        $golongan = $this->data['golongan'] ?? null;

        // Check if the same record exists
        $exists = GolonganBbm::where('golongan', ucwords($golongan))
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Jenis Alut "'.ucwords($golongan).'" sudah ada')
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
            ->body('Data Jenis Alut berhasil ditambahkan.');
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
        return 'Buat Jenis Alut';
    }
}
