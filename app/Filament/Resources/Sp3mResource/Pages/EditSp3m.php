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
        $data['nomor_sp3m'] = strtoupper($data['nomor_sp3m']);
        
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
        $id = $this->record->sp3m_id;
        
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

        $nomorSp3m = strtoupper($this->data['nomor_sp3m']);

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
