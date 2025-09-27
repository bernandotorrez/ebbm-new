<?php

namespace App\Filament\Resources\KantorSarResource\Pages;

use App\Filament\Resources\KantorSarResource;
use App\Models\KantorSar;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditKantorSar extends EditRecord
{
    protected static string $resource = KantorSarResource::class;

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
        $data['kantor_sar'] = ucwords($data['kantor_sar']);

        return $data;
    }

    protected function beforeSave(): void
    {
        // Get input values
        $id = $this->data['kantor_sar_id'] ?? null;
        $kantorSar = $this->data['kantor_sar'] ?? null;

        // Check if the same record exists
        $exists = KantorSar::where('kantor_sar', ucwords($kantorSar))
            ->where('kantor_sar_id', '!=', $id)
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Kantor SAR "'.ucwords($kantorSar).'" sudah ada')
                ->danger()
                ->send();

            // Prevent form submission
            $this->halt();
        }
    }
}
