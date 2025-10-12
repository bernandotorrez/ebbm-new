<?php

namespace App\Filament\Resources\GolonganBbmResource\Pages;

use App\Filament\Resources\GolonganBbmResource;
use App\Models\GolonganBbm;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditGolonganBbm extends EditRecord
{
    protected static string $resource = GolonganBbmResource::class;

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
        $data['golongan'] = ucwords($data['golongan']);

        return $data;
    }

    protected function beforeSave(): void
    {
        // Get input values
        $id = $this->data['golongan_bbm_id'] ?? null;
        $golongan = $this->data['golongan'] ?? null;

        // Check if the same record exists
        $exists = GolonganBbm::where('golongan', ucwords($golongan))
            ->where('golongan_bbm_id', '!=', $id) // Exclude the current record
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Golongan BBM "'.ucwords($golongan).'" sudah ada')
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
            ->body('Data golongan BBM berhasil diperbarui.');
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
}
