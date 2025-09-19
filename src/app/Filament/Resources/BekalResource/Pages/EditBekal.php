<?php

namespace App\Filament\Resources\BekalResource\Pages;

use App\Filament\Resources\BekalResource;
use App\Models\Bekal;
use App\Models\GolonganBbm;
use App\Models\Satuan;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBekal extends EditRecord
{
    protected static string $resource = BekalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Apply ucwords() to the 'bekal' field before saving
        $data['golongan_bbm_id'] = $data['golongan_bbm_id'];
        $data['satuan_id'] = $data['satuan_id'];
        $data['bekal'] = ucwords($data['bekal']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function beforeSave(): void
    {
        // Get input values
        $id = $this->data['bekal_id'] ?? null;
        $golonganBbmId = $this->data['golongan_bbm_id'] ?? null;
        $satuanId = $this->data['satuan_id'] ?? null;
        $bekal = $this->data['bekal'] ?? null;

        // Check if the same record exists
        $exists = Bekal::where('golongan_bbm_id', $golonganBbmId)
            ->where('satuan_id', $satuanId)
            ->where('bekal', ucwords($bekal))
            ->where('bekal_id', '!=', $id)
            ->exists();

        if ($exists) {
            // Show Filament error notification
            $dataGolonganBbm = GolonganBbm::find($golonganBbmId);
            $dataSatuan = Satuan::find($satuanId);

            $message = 'Bekal "'.ucwords($bekal).'" untuk Golongan BBM "'.ucwords($dataGolonganBbm->golongan).'" dan Satuan "'.ucwords($dataSatuan->satuan).'" sudah ada';

            Notification::make()
                ->title('Error!')
                ->body($message)
                ->danger()
                ->send();

            // Prevent form submission
            $this->halt();
        }
    }
}
