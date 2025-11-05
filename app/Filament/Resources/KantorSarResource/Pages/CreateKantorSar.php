<?php

namespace App\Filament\Resources\KantorSarResource\Pages;

use App\Filament\Resources\KantorSarResource;
use App\Models\KantorSar;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateKantorSar extends CreateRecord
{
    protected static string $resource = KantorSarResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Apply ucwords() to the 'bekal' field before saving
        $data['kantor_sar'] = ucwords($data['kantor_sar']);

        return $data;
    }

    protected function beforeCreate(): void
    {
        // Get input values
        $kantorSar = $this->data['kantor_sar'] ?? null;

        // Check if the same record exists
        $exists = KantorSar::where('kantor_sar', ucwords($kantorSar))
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

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data kantor SAR berhasil ditambahkan.');
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
        return 'Buat Kantor SAR';
    }
}
