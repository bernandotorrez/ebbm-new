<?php

namespace App\Filament\Resources\AlpalResource\Pages;

use App\Filament\Resources\AlpalResource;
use App\Models\Alpal;
use App\Models\KantorSar;
use App\Models\PosSandar;
use App\Models\Satuan;
use App\Models\Tbbm;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAlpal extends CreateRecord
{
    protected static string $resource = AlpalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Apply ucwords() to the 'bekal' field before saving
        $data['alpal'] = strtoupper($data['alpal']);

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
        $kantorSarId = $this->data['kantor_sar_id'] ?? null;
        $tbbmId = $this->data['tbbm_id'] ?? null;
        $posSandarId = $this->data['pos_sandar_id'] ?? null;
        $alpal = $this->data['alpal'] ?? null;

        // Check if the same record exists
        $exists = Alpal::where('kantor_sar_id', $kantorSarId)
            ->where('tbbm_id', $tbbmId)
            ->where('pos_sandar_id', $posSandarId)
            ->where('alpal', strtoupper($alpal))
            ->exists();

        if ($exists) {
            // Show Filament error notification
            $dataKantorSar = KantorSar::find($kantorSarId);
            $dataTbbm = Tbbm::find($tbbmId);
            $dataPosSandar = PosSandar::find($posSandarId);

            $message = 'Nama Kapal/No.Reg Pesawat "'.strtoupper($alpal).'" untuk Kantor SAR "'.ucwords($dataKantorSar->kantor_sar).'", TBBM "'.ucwords($dataTbbm->depot).'" dan Pos Sandar "'.ucwords($dataPosSandar->pos_sandar).'" sudah ada';

            Notification::make()
                ->title('Kesalahan!')
                ->body($message)
                ->danger()
                ->send();

            // Prevent form submission
            $this->halt();
        }
    }
}
