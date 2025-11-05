<?php

namespace App\Filament\Resources\PosSandarResource\Pages;

use App\Filament\Resources\PosSandarResource;
use App\Models\PosSandar;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPosSandar extends EditRecord
{
    protected static string $resource = PosSandarResource::class;

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
        $data['pos_sandar'] = ucwords($data['pos_sandar']);

        return $data;
    }

    protected function beforeSave(): void
    {
        // Get input values
        $id = $this->data['pos_sandar_id'] ?? null;
        $posSandar = $this->data['pos_sandar'] ?? null;

        // Check if the same record exists
        $exists = PosSandar::where('pos_sandar', ucwords($posSandar))
            ->where('pos_sandar_id', '!=', $id) // Exclude the current record
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Pos Sandar "'.ucwords($posSandar).'" sudah ada')
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
            ->body('Data pos sandar berhasil diperbarui.');
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
        return 'Ubah Pos Sandar';
    }
}
