<?php

namespace App\Filament\Resources\KotaResource\Pages;

use App\Filament\Resources\KotaResource;
use App\Models\Kota;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditKota extends EditRecord
{
    protected static string $resource = KotaResource::class;

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
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Apply ucwords() to the 'bekal' field before saving
        $data['kota'] = ucwords($data['kota']);

        return $data;
    }

    protected function beforeSave(): void
    {
        // Get input values
        $id = $this->data['kota_id'] ?? null;
        $kota = $this->data['kota'] ?? null;

        // Check if the same record exists
        $exists = Kota::where('kota', ucwords($kota))
            ->where('satuan_id', '!=', $id) // Exclude the current record
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
