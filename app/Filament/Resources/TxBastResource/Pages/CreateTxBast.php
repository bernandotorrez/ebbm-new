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
        
        // Process details - qty_diterima dan qty_terutang sudah dihitung di form secara live
        if (isset($data['details'])) {
            foreach ($data['details'] as $key => &$detail) {
                // Jika qty_masuk kosong, set ke 0
                if (!isset($detail['qty_masuk']) || $detail['qty_masuk'] === null || $detail['qty_masuk'] === '') {
                    $detail['qty_masuk'] = 0;
                }
                
                $qtyMasuk = (int) $detail['qty_masuk'];
                
                // Get harga satuan from pelumas
                $pelumas = Pelumas::find($detail['pelumas_id']);
                $hargaSatuan = $pelumas ? $pelumas->harga : 0;
                
                // Pastikan semua field harga ada dengan nilai default 0
                $detail['jumlah_harga_mulai'] = $detail['jumlah_harga_mulai'] ?? 0;
                $detail['jumlah_harga_diterima'] = $detail['jumlah_harga_diterima'] ?? 0;
                $detail['jumlah_harga_terutang'] = $detail['jumlah_harga_terutang'] ?? 0;
                
                // Calculate harga masuk
                $detail['jumlah_harga_masuk'] = $qtyMasuk * $hargaSatuan;
                
                $detail['created_by'] = Auth::user()->user_id;
            }
            
            // Unset reference
            unset($detail);
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Refresh record untuk mendapatkan data terbaru dari database
        $this->record->refresh();
        $this->record->load('details');
        
        // Check if all details are completed (qty_terutang = 0 untuk semua detail)
        $allCompleted = true;
        foreach ($this->record->details as $detail) {
            // Jika masih ada qty_terutang > 0, berarti belum selesai
            if ((int) $detail->qty_terutang > 0) {
                $allCompleted = false;
                break;
            }
        }
        
        // Update bast_sudah_diterima_semua di SP3K jika semua sudah selesai
        if ($allCompleted && $this->record->sp3k) {
            $this->record->sp3k->bast_sudah_diterima_semua = '1';
            $this->record->sp3k->save();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
