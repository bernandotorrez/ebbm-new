<?php

namespace App\Filament\Resources\Sp3kResource\Pages;

use App\Filament\Resources\Sp3kResource;
use App\Models\KantorSar;
use App\Models\Pelumas;
use App\Models\TxSp3k;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSp3k extends CreateRecord
{
    protected static string $resource = Sp3kResource::class;

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
        return 'Buat SP3K';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set created_by to current user
        $data['created_by'] = Auth::id();
        
        // Calculate jumlah_qty and jumlah_harga from details
        $jumlahQty = 0;
        $jumlahHarga = 0;
        
        if (isset($data['details'])) {
            foreach ($data['details'] as $index => $detail) {
                // Get harga from pelumas model
                $pelumas = Pelumas::find($detail['pelumas_id']);
                $harga = $pelumas ? $pelumas->harga : 0;
                
                $jumlahQty += $detail['qty'];
                $jumlahHarga += $detail['qty'] * $harga;
                // Set sort order
                $data['details'][$index]['sort'] = $index;
                // Set harga in details for database storage
                $data['details'][$index]['harga'] = $harga;
            }
        }
        
        $data['jumlah_qty'] = $jumlahQty;
        $data['jumlah_harga'] = $jumlahHarga;
        
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
        $tahunAnggaran = $this->data['tahun_anggaran'] ?? null;
        $tw = $this->data['tw'] ?? null;

        // Check if the same record exists
        $exists = TxSp3k::where('kantor_sar_id', $kantorSarId)
            ->where('tahun_anggaran', $tahunAnggaran)
            ->where('tw', $tw)
            ->exists();

        if ($exists) {
            // Show Filament error notification
            $dataKantorSar = KantorSar::find($kantorSarId);

            $message = 'Kantor SAR "'.ucwords($dataKantorSar->kantor_sar??'').'", Tahun Anggaran "'.$tahunAnggaran.'" dan Triwulan "'.$tw.'" sudah ada';

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