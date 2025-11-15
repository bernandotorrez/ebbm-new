<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use App\Models\DeliveryOrder;
use App\Models\Sp3m;
use App\Models\Tbbm;
use App\Models\HargaBekal;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryOrder extends CreateRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Clean numeric fields
        $data['qty'] = (int) preg_replace('/[^\d]/', '', $data['qty']);
        
        // Get kota_id from SP3M -> Alpal -> TBBM
        $sp3mId = $data['sp3m_id'] ?? null;
        if ($sp3mId) {
            $sp3m = Sp3m::with(['alpal.tbbm'])->find($sp3mId);
            
            if ($sp3m && $sp3m->alpal && $sp3m->alpal->tbbm) {
                $kotaId = $sp3m->alpal->tbbm->kota_id;
                $bekalId = $sp3m->bekal_id;
                
                // Get harga from ms_harga_bekal based on kota_id and bekal_id
                $hargaBekal = HargaBekal::where('kota_id', $kotaId)
                    ->where('bekal_id', $bekalId)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($hargaBekal) {
                    $data['harga_bekal_id'] = $hargaBekal->harga_bekal_id;
                    $harga = $hargaBekal->harga;
                    
                    // Calculate jumlah_harga = qty * harga
                    $data['jumlah_harga'] = (int) ($data['qty'] * $harga);
                } else {
                    // Fallback jika tidak ada harga bekal
                    $data['harga_bekal_id'] = null;
                    $data['jumlah_harga'] = 0;
                }
            } else {
                $data['harga_bekal_id'] = null;
                $data['jumlah_harga'] = 0;
            }
        }
        
        // Remove PPN and PBBKB (tidak digunakan lagi)
        unset($data['ppn']);
        unset($data['pbbkb']);

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
        $sp3mId = $this->data['sp3m_id'] ?? null;
        $tbbmId = $this->data['tbbm_id'] ?? null;
        $tahunAnggaran = $this->data['tahun_anggaran'] ?? null;
        $qty = (int) preg_replace('/[^\d]/', '', $this->data['qty'] ?? 0);

        // Validasi sisa_qty di SP3M
        $sp3m = Sp3m::with(['alpal.tbbm', 'bekal', 'kantorSar'])->find($sp3mId);
        
        if (!$sp3m) {
            Notification::make()
                ->title('Gagal Membuat Delivery Order!')
                ->body('SP3M tidak ditemukan.')
                ->danger()
                ->duration(5000)
                ->send();
            $this->halt();
        }

        // Validasi harga_bekal_id
        if ($sp3m->alpal && $sp3m->alpal->tbbm) {
            $kotaId = $sp3m->alpal->tbbm->kota_id;
            $bekalId = $sp3m->bekal_id;
            
            $hargaBekal = \App\Models\HargaBekal::where('kota_id', $kotaId)
                ->where('bekal_id', $bekalId)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$hargaBekal) {
                $kotaName = $sp3m->alpal->tbbm->kota->kota ?? 'Unknown';
                $bekalName = $sp3m->bekal->bekal ?? 'Unknown';
                
                Notification::make()
                    ->title('Gagal Membuat Delivery Order!')
                    ->body("Harga bekal tidak ditemukan untuk Kota: {$kotaName} dan Jenis Bahan Bakar: {$bekalName}. Silakan hubungi administrator untuk menambahkan data harga bekal.")
                    ->danger()
                    ->duration(10000)
                    ->send();
                $this->halt();
            }
        } else {
            Notification::make()
                ->title('Gagal Membuat Delivery Order!')
                ->body('Data Alpal atau TBBM tidak lengkap pada SP3M yang dipilih.')
                ->danger()
                ->duration(7000)
                ->send();
            $this->halt();
        }

        // Cek apakah sisa_qty mencukupi
        if ($sp3m->sisa_qty < $qty) {
            $qtyFormatted = number_format($qty, 0, ',', '.');
            $sisaQtyFormatted = number_format($sp3m->sisa_qty, 0, ',', '.');
            
            Notification::make()
                ->title('Gagal Membuat Delivery Order!')
                ->body("Qty DO ({$qtyFormatted}) melebihi sisa qty SP3M ({$sisaQtyFormatted}). Silakan kurangi qty atau pilih SP3M lain.")
                ->danger()
                ->duration(7000)
                ->send();
            $this->halt();
        }

        // Kurangi sisa_qty di SP3M
        $sp3m->sisa_qty -= $qty;
        $sp3m->save();
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data delivery order berhasil ditambahkan.');
    }

    public function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Buat')
                ->disabled(function () {
                    return $this->isQtyInvalid();
                }),
            $this->getCreateAnotherFormAction()
                ->label('Buat & Buat lainnya')
                ->disabled(function () {
                    return $this->isQtyInvalid();
                }),
            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }
    
    protected function isQtyInvalid(): bool
    {
        $sp3mId = $this->data['sp3m_id'] ?? null;
        $qty = (int) preg_replace('/[^\d]/', '', $this->data['qty'] ?? 0);
        
        if (!$sp3mId || $qty <= 0) {
            return false;
        }
        
        $sp3m = Sp3m::find($sp3mId);
        if (!$sp3m) {
            return false;
        }
        
        return $qty > $sp3m->sisa_qty;
    }
    
    public function getTitle(): string
    {
        return 'Buat Delivery Order';
    }
}
