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
        // Simpan data lampiran sementara dan hapus dari data SP3M
        $lampiranData = $data['lampiran'] ?? [];
        unset($data['lampiran']);
        
        // Simpan ke property untuk digunakan di afterCreate
        $this->lampiranData = $lampiranData;
        
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
        
        // Calculate harga_satuan from HargaBekal berdasarkan bekal_id dan wilayah_id
        $bekalId = $data['bekal_id'] ?? null;
        $kantorSarId = $data['kantor_sar_id'] ?? null;
        
        if ($bekalId && $kantorSarId) {
            // Ambil wilayah_id dari kantor_sar -> kota -> wilayah
            $kantorSar = KantorSar::with('kota.wilayah')->find($kantorSarId);
            $wilayahId = $kantorSar?->kota?->wilayah_id;
            
            if ($wilayahId) {
                // Cari harga bekal terbaru berdasarkan tanggal_update
                $hargaBekal = HargaBekal::where('bekal_id', $bekalId)
                    ->where('wilayah_id', $wilayahId)
                    ->whereNotNull('tanggal_update')
                    ->orderBy('tanggal_update', 'desc')
                    ->first();
                
                $data['harga_satuan'] = $hargaBekal ? (int) $hargaBekal->harga : 0;
            } else {
                $data['harga_satuan'] = 0;
            }
        } else {
            $data['harga_satuan'] = 0;
        }
        
        // Calculate jumlah_harga = qty * harga_satuan
        $data['jumlah_harga'] = $data['qty'] * $data['harga_satuan'];
        
        // Set sisa_qty sama dengan qty
        $data['sisa_qty'] = $data['qty'];

        return $data;
    }

    protected function afterCreate(): void
    {
        // Simpan lampiran setelah SP3M dibuat
        if (!empty($this->lampiranData)) {
            foreach ($this->lampiranData as $lampiran) {
                // Trait PreventUpdateTimestamp akan otomatis set created_by
                $this->record->lampiran()->create([
                    'nama_file' => $lampiran['nama_file'],
                    'file_path' => $lampiran['file_path'],
                    'keterangan' => $lampiran['keterangan'] ?? null,
                ]);
            }
        }
    }

    protected $lampiranData = [];

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }
}
