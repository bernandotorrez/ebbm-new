<?php

namespace App\Filament\Resources\TxBastResource\Pages;

use App\Filament\Resources\TxBastResource;
use App\Models\Pelumas;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditTxBast extends EditRecord
{
    protected static string $resource = TxBastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::user()->user_id;
        
        // Process details
        if (isset($data['details'])) {
            foreach ($data['details'] as &$detail) {
                // Get original detail to calculate difference
                $originalDetail = $this->record->details()->find($detail['detail_bast_id']);
                
                if ($originalDetail) {
                    // Calculate difference in qty_masuk
                    $oldQtyMasuk = $originalDetail->qty_masuk;
                    $newQtyMasuk = (int) $detail['qty_masuk'];
                    $qtyDiff = $newQtyMasuk - $oldQtyMasuk;
                    
                    // Update qty_diterima dan qty_terutang
                    $detail['qty_diterima'] = $originalDetail->qty_diterima - $oldQtyMasuk + $newQtyMasuk;
                    $detail['qty_terutang'] = (int) $detail['qty_mulai'] - $detail['qty_diterima'];
                    
                    // Get harga satuan from pelumas
                    $pelumas = Pelumas::find($detail['pelumas_id']);
                    $hargaSatuan = $pelumas ? $pelumas->harga : 0;
                    
                    // Calculate harga
                    $detail['jumlah_harga_masuk'] = $newQtyMasuk * $hargaSatuan;
                    $oldHargaMasuk = $originalDetail->jumlah_harga_masuk;
                    $detail['jumlah_harga_diterima'] = $originalDetail->jumlah_harga_diterima - $oldHargaMasuk + $detail['jumlah_harga_masuk'];
                    $detail['jumlah_harga_terutang'] = (float) $detail['jumlah_harga_mulai'] - $detail['jumlah_harga_diterima'];
                }
                
                $detail['updated_by'] = Auth::user()->user_id;
            }
        }
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Refresh record untuk mendapatkan data terbaru dari database
        $this->record->refresh();
        $this->record->load('details', 'sp3k');
        
        // Check if all details are completed (qty_terutang = 0 untuk semua detail)
        $allCompleted = true;
        foreach ($this->record->details as $detail) {
            if ((int) $detail->qty_terutang > 0) {
                $allCompleted = false;
                break;
            }
        }
        
        // Update bast_sudah_diterima_semua di SP3K
        if ($this->record->sp3k) {
            $this->record->sp3k->bast_sudah_diterima_semua = $allCompleted ? '1' : '0';
            $this->record->sp3k->save();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
