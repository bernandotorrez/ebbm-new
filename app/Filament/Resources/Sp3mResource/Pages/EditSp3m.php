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
use Filament\Resources\Pages\EditRecord;

class EditSp3m extends EditRecord
{
    protected static string $resource = Sp3mResource::class;

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
        // Clean numeric fields
        $data['qty'] = (int) preg_replace('/[^\d]/', '', $data['qty']);
        $data['harga_satuan'] = (int) preg_replace('/[^\d]/', '', $data['harga_satuan']);
        $data['jumlah_harga'] = (int) preg_replace('/[^\d]/', '', $data['jumlah_harga']);
        
        // Calculate sisa_qty based on whether SP3M has DO or not
        $oldQty = $this->record->qty;
        $newQty = $data['qty'];
        $oldSisaQty = $this->record->sisa_qty;
        
        // Calculate the difference
        $qtyDiff = $newQty - $oldQty;
        
        // Update sisa_qty: sisa_qty_baru = sisa_qty_lama + (qty_baru - qty_lama)
        $data['sisa_qty'] = $oldSisaQty + $qtyDiff;

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
        $id = $this->data['sp3m_id'] ?? null;
        $kantorSarId = $this->data['kantor_sar_id'] ?? null;
        $alpalId = $this->data['alpal_id'] ?? null;
        $bekalId = $this->data['bekal_id'] ?? null;
        $tahunAnggaran = $this->data['tahun_anggaran'] ?? null;
        
        // Validate Qty update based on DO existence
        $newQty = (int) preg_replace('/[^\d]/', '', $this->data['qty'] ?? 0);
        $oldQty = $this->record->qty;
        $oldSisaQty = $this->record->sisa_qty;
        
        // Check if SP3M has any DO
        $hasDo = \App\Models\DeliveryOrder::where('sp3m_id', $id)->exists();
        
        if ($hasDo) {
            // SP3M sudah memiliki DO
            // Qty tidak boleh kurang dari sisa_qty
            if ($newQty < $oldSisaQty) {
                $newQtyFormatted = number_format($newQty, 0, ',', '.');
                $sisaQtyFormatted = number_format($oldSisaQty, 0, ',', '.');
                
                Notification::make()
                    ->title('Gagal Mengubah SP3M!')
                    ->body("Qty baru ({$newQtyFormatted}) tidak boleh kurang dari Sisa Qty ({$sisaQtyFormatted}) karena SP3M ini sudah memiliki Delivery Order.")
                    ->danger()
                    ->duration(7000)
                    ->send();
                $this->halt();
            }
        }
        // Jika belum memiliki DO, qty bisa diubah berapapun dan sisa_qty akan sama dengan qty

        // If harga_satuan is missing (readonly/derived), derive from latest HargaBekal for the bekal
        $hargaSatuan = $this->data['harga_satuan'] ?? null;
        if ((empty($hargaSatuan) || $hargaSatuan === 0) && $bekalId) {
            $harga = HargaBekal::where('bekal_id', $bekalId)
                ->orderBy('created_at', 'desc')
                ->value('harga');

            $this->data['harga_satuan'] = $harga !== null ? (int) $harga : $this->data['harga_satuan'] ?? null;
        }

        $nomorSp3m    = strtoupper($this->data['nomor_sp3m']);

        $duplicateSp3kNumber = Sp3m::where('nomor_sp3m', $nomorSp3m)
            ->where('sp3m_id', '!=', $id)
            ->exists();

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
            ->where('sp3m_id', '!=', $id)
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
            ->body('Data SP3M berhasil diperbarui.');
    }

    public function getTitle(): string
    {
        return 'Ubah SP3M';
    }
}
