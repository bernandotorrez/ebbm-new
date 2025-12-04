<?php

namespace App\Filament\Resources\Sp3kResource\Pages;

use App\Filament\Resources\Sp3kResource;
use App\Models\KantorSar;
use App\Models\Pelumas;
use App\Models\TxSp3k;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSp3k extends CreateRecord
{
    protected static string $resource = Sp3kResource::class;

    public function getTitle(): string
    {
        return 'Buat SP3K';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Buat'),
            $this->getCreateAnotherFormAction()->label('Buat & Buat lainnya'),
            $this->getCancelFormAction()->label('Batal'),
        ];
    }

    /**
     * Isi nilai awal supaya insert ke table utama nggak error.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate nomor SP3K di backend untuk menghindari duplikasi
        if (!empty($data['alpal_id'])) {
            $tahunAnggaran = $data['tahun_anggaran'] ?? null;
            $data['nomor_sp3k'] = \DB::transaction(function () use ($data, $tahunAnggaran) {
                return Sp3kResource::generateNomorSp3k($data['alpal_id'], $tahunAnggaran);
            });
        }

        // pastikan kolom NOT NULL punya nilai
        $data['jumlah_qty'] = 0;
        $data['jumlah_harga'] = 0;
        $data['jumlah_liter'] = 0;

        return $data;
    }

    protected function beforeCreate(): void
    {
        // Validasi minimal 1 detail
        $details = $this->data['details'] ?? [];
        if (empty($details) || count($details) < 1) {
            Notification::make()
                ->title('Kesalahan!')
                ->body('Minimal harus ada 1 detail pelumas.')
                ->danger()
                ->send();
            $this->halt();
        }

        $kantorSarId   = $this->data['kantor_sar_id'] ?? null;
        $tahunAnggaran = $this->data['tahun_anggaran'] ?? null;
        $tw            = $this->data['tw'] ?? null;

        $exists = TxSp3k::where('kantor_sar_id', $kantorSarId)
            ->where('tahun_anggaran', $tahunAnggaran)
            ->where('tw', $tw)
            ->exists();

        if ($exists) {
            $dataKantorSar = KantorSar::find($kantorSarId);

            $message = 'Kantor SAR "' . ucwords($dataKantorSar->kantor_sar ?? '') . '", Tahun Anggaran "' . $tahunAnggaran . '" dan Triwulan "' . $tw . '" sudah ada';

            Notification::make()
                ->title('Kesalahan!')
                ->body($message)
                ->danger()
                ->send();

            $this->halt();
        }
    }

    /**
     * Setelah record master-nya ke-save dan repeater relasinya ke-save
     * baru kita hitung ulang dan update.
     */
    protected function afterCreate(): void
    {
        $record = $this->record;

        $jumlahQty   = 0;
        $jumlahHarga = 0;
        $jumlahLiter = 0;

        // ambil semua detail
        $details = $record->details()->get();

        foreach ($details as $index => $detail) {
            $harga = 0;
            $liter = 0;

            if ($detail->pelumas_id) {
                // load pelumas + relasi kemasan
                $pelumas = Pelumas::with('kemasan')->find($detail->pelumas_id);
                if ($pelumas) {
                    // asumsi harga ada di tabel pelumas
                    $harga = $pelumas->harga ?? 0;
                    // asumsi relasi: Pelumas belongsTo Kemasan dan kemasan punya field 'liter'
                    $liter = $pelumas->kemasan->kemasan_liter ?? 0;
                }
            }

            $qty = (int) ($detail->qty ?? 0);

            // akumulasi ke master
            $jumlahQty   += $qty;
            $jumlahHarga += $qty * $harga;
            $jumlahLiter += $qty * $liter;

            // simpan kembali ke detail
            // kalau kamu mau simpan total per baris:
            $detail->harga = $qty * $harga;
            $detail->liter = $qty * $liter;

            // urutan
            $detail->sort  = $index;

            $detail->save();
        }

        // update ke master
        $record->update([
            'jumlah_qty'   => $jumlahQty,
            'jumlah_harga' => $jumlahHarga,
            'jumlah_liter' => $jumlahLiter,
        ]);
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
