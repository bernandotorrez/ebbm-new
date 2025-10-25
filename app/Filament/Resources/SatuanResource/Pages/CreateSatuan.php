<?php

namespace App\Filament\Resources\SatuanResource\Pages;

use App\Filament\Resources\SatuanResource;
use App\Models\Satuan;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\Client\Request;

class CreateSatuan extends CreateRecord
{
    protected static string $resource = SatuanResource::class;

    protected function getFormActions(): array
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

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Apply ucwords() to the 'bekal' field before saving
        $data['satuan'] = ucwords($data['satuan']);

        return $data;
    }

    protected function beforeCreate(): void
    {
        // Get input values
        $satuan = $this->data['satuan'] ?? null;

        // Check if the same record exists
        $exists = Satuan::where('satuan', ucwords($satuan))
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Kesalahan!')
                ->body('Satuan "'.ucwords($satuan).'" sudah ada')
                ->danger()
                ->send();

            // Prevent form submission
            $this->halt();
        }
    }

    public function getTitle(): string
    {
        return 'Buat Satuan';
    }
}
