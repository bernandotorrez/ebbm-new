<?php

namespace App\Filament\Resources\SatuanResource\Pages;

use App\Filament\Resources\SatuanResource;
use App\Models\Satuan;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSatuan extends EditRecord
{
    protected static string $resource = SatuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
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
        $data['satuan'] = ucwords($data['satuan']);

        return $data;
    }

    protected function beforeSave(): void
    {
        // Get input values
        $id = $this->data['satuan_id'] ?? null;
        $satuan = $this->data['satuan'] ?? null;

        // Check if the same record exists
        $exists = Satuan::where('satuan', ucwords($satuan))
            ->where('satuan_id', '!=', $id) // Exclude the current record
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Satuan "'.ucwords($satuan).'" sudah ada')
                ->danger()
                ->send();

            // Prevent form submission
            $this->halt();
        }
    }
}
