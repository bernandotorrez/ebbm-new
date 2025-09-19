<?php

namespace App\Filament\Resources\KotaResource\Pages;

use App\Filament\Resources\KotaResource;
use App\Models\Kota;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateKota extends CreateRecord
{
    protected static string $resource = KotaResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Apply ucwords() to the 'bekal' field before saving
        $data['kota'] = ucwords($data['kota']);

        return $data;
    }

    protected function beforeCreate(): void
    {
        // Get input values
        $kota = $this->data['kota'] ?? null;

        // Check if the same record exists
        $exists = Kota::where('kota', ucwords($kota))
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Kota "'.ucwords($kota).'" sudah ada')
                ->danger()
                ->send();

            // Prevent form submission
            $this->halt();
        }
    }
}
