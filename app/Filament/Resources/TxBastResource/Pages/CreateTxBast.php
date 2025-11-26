<?php

namespace App\Filament\Resources\TxBastResource\Pages;

use App\Filament\Resources\TxBastResource;
use App\Models\Pelumas;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTxBast extends CreateRecord
{
    protected static string $resource = TxBastResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::user()->user_id;
        
        // Process details
        if (isset($data['details'])) {
            foreach ($data['details'] as &$detail) {
                $qtyMasuk = (int) $detail['qty_masuk'];
                $qtyDiterima = (int) $detail['qty_diterima'];
                $qtyMulai = (int) $detail['qty_mulai'];
                
                // Update qty_diterima dan qty_terutang
                $detail['qty_diterima'] = $qtyDiterima + $qtyMasuk;
                $detail['qty_terutang'] = $qtyMulai - $detail['qty_diterima'];
                
                // Get harga satuan from pelumas
                $pelumas = Pelumas::find($detail['pelumas_id']);
                $hargaSatuan = $pelumas ? $pelumas->harga : 0;
                
                // Calculate harga
                $detail['jumlah_harga_masuk'] = $qtyMasuk * $hargaSatuan;
                $detail['jumlah_harga_diterima'] = (float) $detail['jumlah_harga_diterima'] + $detail['jumlah_harga_masuk'];
                $detail['jumlah_harga_terutang'] = (float) $detail['jumlah_harga_mulai'] - $detail['jumlah_harga_diterima'];
                
                $detail['created_by'] = Auth::user()->user_id;
            }
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Check if all details are completed
        $allCompleted = true;
        foreach ($this->record->details as $detail) {
            if ($detail->qty_terutang > 0) {
                $allCompleted = false;
                break;
            }
        }
        
        // Update sudah_diterima_semua
        $this->record->sudah_diterima_semua = $allCompleted ? '1' : '0';
        $this->record->save();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
