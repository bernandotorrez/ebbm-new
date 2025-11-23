<?php

namespace App\Filament\Resources\TbbmResource\Pages;

use App\Filament\Resources\TbbmResource;
use App\Models\Kota;
use App\Models\Tbbm;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTbbm extends CreateRecord
{
    protected static string $resource = TbbmResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Apply ucwords() to the 'bekal' field before saving
        $data['kota_id'] = $data['kota_id'];
        $data['plant'] = $data['plant'];
        $data['depot'] = ucwords($data['depot']);
        $data['pbbkb'] = $data['pbbkb'];
        // $data['ship_to'] = $data['ship_to'];

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        // Get input values
        $kotaId = $this->data['kota_id'] ?? null;
        $plant = $this->data['plant'] ?? null;
        $depot = $this->data['depot'] ?? null;

        // Check if the same record exists
        $exists = Tbbm::where('plant', $plant)
        ->orWhere(function ($query) use ($kotaId, $depot) {
            $query->where('kota_id', $kotaId)
                ->where('depot', ucwords($depot));
        })
        ->exists();


        if ($exists) {
            // Show Filament error notification
            $dataGolonganBbm = Kota::find($kotaId);

            $message = 'Plant "'.$plant.'" atau Kota "'.ucwords($dataGolonganBbm->kota).'" dan Depot "'.ucwords($depot).'" sudah ada';

            Notification::make()
                ->title('Error!')
                ->body($message)
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
            ->body('Data TBBM berhasil ditambahkan.');
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
        return 'Buat TBBM/DDPU';
    }
}
