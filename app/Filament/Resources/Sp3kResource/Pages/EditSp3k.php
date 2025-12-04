<?php

namespace App\Filament\Resources\Sp3kResource\Pages;

use App\Filament\Resources\Sp3kResource;
use App\Models\KantorSar;
use App\Models\Pelumas;
use App\Models\TxSp3k;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSp3k extends EditRecord
{
    protected static string $resource = Sp3kResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan'),
            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Cek apakah nomor SP3K berubah (alut atau tahun anggaran berubah)
        $nomorSp3kChanged = $data['nomor_sp3k_changed'] ?? false;
        $originalAlpalId = $data['original_alpal_id'] ?? null;
        $currentAlpalId = $data['alpal_id'] ?? null;
        $originalTahunAnggaran = $data['original_tahun_anggaran'] ?? null;
        $currentTahunAnggaran = $data['tahun_anggaran'] ?? null;
        
        // Jika alut dan tahun anggaran kembali ke original, gunakan nomor original
        if ($originalAlpalId && $currentAlpalId && $originalTahunAnggaran && $currentTahunAnggaran &&
            $originalAlpalId == $currentAlpalId && $originalTahunAnggaran == $currentTahunAnggaran) {
            $data['nomor_sp3k'] = $data['original_nomor_sp3k'] ?? $this->record->nomor_sp3k;
        } elseif ($nomorSp3kChanged && !empty($data['alpal_id'])) {
            $tahunAnggaran = $data['tahun_anggaran'] ?? null;
            $data['nomor_sp3k'] = \DB::transaction(function () use ($data, $tahunAnggaran) {
                return Sp3kResource::generateNomorSp3k($data['alpal_id'], $tahunAnggaran);
            });
        } else {
            $data['nomor_sp3k'] = $this->record->nomor_sp3k;
        }

        // Set updated_by to current user ID (not username)
        $userId = Auth::id();
        $data['updated_by'] = $userId;

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

    protected function beforeSave(): void
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
    }

    protected function beforeSaveOld(): void
    {
        // Get input values
        $id = $this->data['sp3k_id'] ?? null;
        $kantorSarId = $this->data['kantor_sar_id'] ?? null;
        $tahunAnggaran = $this->data['tahun_anggaran'] ?? null;
        $tw = $this->data['tw'] ?? null;
        $nomor_sp3k    = $this->data['nomor_sp3k'];

        $duplicateSp3kNumber = TxSp3k::where('nomor_sp3k', $nomor_sp3k)
            ->where('sp3k_id', '!=', $id)
            ->exists();

        if ($duplicateSp3kNumber) {
            $message = 'Nomor SP3K : '.$nomor_sp3k.' Sudah ada';

            Notification::make()
                ->title('Kesalahan!')
                ->body($message)
                ->danger()
                ->send();

            $this->halt();
        }

        // Check if the same record exists
        $exists = TxSp3k::where('kantor_sar_id', $kantorSarId)
            ->where('tahun_anggaran', $tahunAnggaran)
            ->where('tw', $tw)
            ->where('sp3k_id', '!=', $id)
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus'),
            Actions\ForceDeleteAction::make()
                ->label('Hapus Permanen'),
            Actions\RestoreAction::make()
                ->label('Pulihkan'),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data SP3K berhasil diperbarui.');
    }

    public function getTitle(): string
    {
        return 'Ubah SP3K';
    }
}
