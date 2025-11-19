<?php

namespace App\Filament\Resources\Sp3mResource\Pages;

use App\Filament\Resources\Sp3mResource;
use App\Models\Alpal;
use App\Models\Bekal;
use App\Models\KantorSar;
use App\Models\HargaBekal;
use App\Models\Sp3m;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSp3m extends CreateRecord
{
    protected static string $resource = Sp3mResource::class;

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

    public function getTitle(): string
    {
        return 'Buat SP3M';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Apply ucwords() to the 'bekal' field before saving
        $data['qty'] = (int) preg_replace('/[^\d]/', '', $data['qty']);
        $data['harga_satuan'] = (int) preg_replace('/[^\d]/', '', $data['harga_satuan']);
        $data['jumlah_harga'] = (int) preg_replace('/[^\d]/', '', $data['jumlah_harga']);
        $data['nomor_sp3m'] = strtoupper($data['nomor_sp3m']);
        
        // Set sisa_qty sama dengan qty
        $data['sisa_qty'] = $data['qty'];

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
        $alpalId = $this->data['alpal_id'] ?? null;
        $bekalId = $this->data['bekal_id'] ?? null;
        $tahunAnggaran = $this->data['tahun_anggaran'] ?? null;

        // If harga_satuan is missing (readonly/derived), derive from latest HargaBekal for the bekal
        $hargaSatuan = $this->data['harga_satuan'] ?? null;
        if ((empty($hargaSatuan) || $hargaSatuan === 0) && $bekalId) {
            $harga = HargaBekal::where('bekal_id', $bekalId)
                ->orderBy('created_at', 'desc')
                ->value('harga');

            $this->data['harga_satuan'] = $harga !== null ? (int) $harga : $this->data['harga_satuan'] ?? null;
        }

        $nomorSp3m    = strtoupper($this->data['nomor_sp3m']);

        $duplicateSp3kNumber = Sp3m::where('nomor_sp3m', $nomorSp3m)->exists();
        if ($duplicateSp3kNumber) {
            $message = 'Nomor SP3M : '.$nomorSp3m.' Sudah ada';

            Notification::make()
                ->title('Kesalahan!')
                ->body($message)
                ->danger()
                ->send();

            $this->halt();
        }

        // Get triwulan
        $tw = $this->data['tw'] ?? null;
        
        // Check if the same record exists (including triwulan)
        $exists = Sp3m::where('kantor_sar_id', $kantorSarId)
            ->where('alpal_id', $alpalId)
            ->where('bekal_id', $bekalId)
            ->where('tahun_anggaran', $tahunAnggaran)
            ->where('tw', $tw)
            ->exists();

        if ($exists) {
            // Show Filament error notification
            $dataKantorSar = KantorSar::find($kantorSarId);
            $dataAlpal = Alpal::find($alpalId);
            $dataBekal = Bekal::find($bekalId);
            
            $triwulanLabel = match($tw) {
                '1' => 'Triwulan I',
                '2' => 'Triwulan II',
                '3' => 'Triwulan III',
                '4' => 'Triwulan IV',
                default => 'Triwulan '.$tw
            };

            $message = 'Data SP3M dengan kombinasi Kantor SAR "'.ucwords($dataKantorSar->kantor_sar).'", Alpal "'.ucwords($dataAlpal->alpal).'", Bekal "'.ucwords($dataBekal->bekal).'", Tahun Anggaran "'.$tahunAnggaran.'" dan '.$triwulanLabel.' sudah ada';

            Notification::make()
                ->title('Kesalahan!')
                ->body($message)
                ->danger()
                ->duration(7000)
                ->send();

            // Prevent form submission
            $this->halt();
        }
    }
}
