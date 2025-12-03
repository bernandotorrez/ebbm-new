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
        // Clean numeric fields
        $data['qty'] = (int) preg_replace('/[^\d]/', '', $data['qty']);
        
        // Generate nomor SP3M di backend untuk menghindari duplikasi
        if (!empty($data['alpal_id'])) {
            $tahunAnggaran = $data['tahun_anggaran'] ?? null;
            $data['nomor_sp3m'] = \DB::transaction(function () use ($data, $tahunAnggaran) {
                return Sp3mResource::generateNomorSp3m($data['alpal_id'], $tahunAnggaran);
            });
        }
        
        // Get kantor_sar_id from Alut if not set
        if (empty($data['kantor_sar_id']) && !empty($data['alpal_id'])) {
            $alpal = Alpal::find($data['alpal_id']);
            if ($alpal && $alpal->kantor_sar_id) {
                $data['kantor_sar_id'] = $alpal->kantor_sar_id;
            }
        }
        
        // Calculate harga_satuan from HargaBekal (get latest harga for the bekal_id)
        $bekalId = $data['bekal_id'] ?? null;
        
        if ($bekalId) {
            $hargaBekal = HargaBekal::where('bekal_id', $bekalId)
                ->orderBy('created_at', 'desc')
                ->first();
            
            $data['harga_satuan'] = $hargaBekal ? (int) $hargaBekal->harga : 0;
        } else {
            $data['harga_satuan'] = 0;
        }
        
        // Calculate jumlah_harga = qty * harga_satuan
        $data['jumlah_harga'] = $data['qty'] * $data['harga_satuan'];
        
        // Set sisa_qty sama dengan qty
        $data['sisa_qty'] = $data['qty'];

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }
}
