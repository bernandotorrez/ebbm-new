<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use App\Models\DeliveryOrder;
use App\Models\Sp3m;
use App\Models\Tbbm;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryOrder extends EditRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan')
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
        $newQty = (int) preg_replace('/[^\d]/', '', $this->data['qty'] ?? 0);
        $oldQty = $this->record->qty ?? 0;
        
        if (!$sp3mId || $newQty <= 0) {
            return false;
        }
        
        $sp3m = Sp3m::find($sp3mId);
        if (!$sp3m) {
            return false;
        }
        
        // Hitung sisa qty yang tersedia (termasuk qty lama dari record ini)
        $availableQty = $sp3m->sisa_qty + $oldQty;
        
        return $newQty > $availableQty;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
                $hargaBekal = \App\Models\HargaBekal::where('kota_id', $kotaId)
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

    protected function beforeSave(): void
    {
        // Get input values
        $id = $this->data['do_id'] ?? null;
        $doId = $this->record->do_id;
        $sp3mId = $this->data['sp3m_id'] ?? null;
        $newQty = (int) preg_replace('/[^\d]/', '', $this->data['qty'] ?? 0);
        $oldQty = $this->record->qty;
        $tbbmId = $this->data['tbbm_id'] ?? null;
        $tahunAnggaran = $this->data['tahun_anggaran'] ?? null;

        // Cek apakah ini DO terakhir dari SP3M
        $latestDo = DeliveryOrder::where('sp3m_id', $sp3mId)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestDo && $latestDo->do_id !== $doId) {
            Notification::make()
                ->title('Error!')
                ->body('Hanya DO terakhir yang dapat diubah. DO ini bukan DO terakhir dari SP3M terkait.')
                ->danger()
                ->send();
            $this->halt();
        }

        // Validasi sisa_qty di SP3M
        $sp3m = Sp3m::with(['alpal.tbbm', 'bekal'])->find($sp3mId);
        
        if (!$sp3m) {
            Notification::make()
                ->title('Error!')
                ->body('SP3M tidak ditemukan.')
                ->danger()
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
                    ->title('Gagal Mengubah Delivery Order!')
                    ->body("Harga bekal tidak ditemukan untuk Kota: {$kotaName} dan Jenis Bahan Bakar: {$bekalName}. Silakan hubungi administrator untuk menambahkan data harga bekal.")
                    ->danger()
                    ->duration(10000)
                    ->send();
                $this->halt();
            }
        } else {
            Notification::make()
                ->title('Gagal Mengubah Delivery Order!')
                ->body('Data Alpal atau TBBM tidak lengkap pada SP3M yang dipilih.')
                ->danger()
                ->duration(7000)
                ->send();
            $this->halt();
        }

        // Hitung selisih qty
        $qtyDiff = $newQty - $oldQty;

        // Cek apakah sisa_qty mencukupi untuk perubahan
        if ($sp3m->sisa_qty < $qtyDiff) {
            $newQtyFormatted = number_format($newQty, 0, ',', '.');
            $availableQty = $sp3m->sisa_qty + $oldQty;
            $availableQtyFormatted = number_format($availableQty, 0, ',', '.');
            
            Notification::make()
                ->title('Gagal Mengubah Delivery Order!')
                ->body("Qty baru ({$newQtyFormatted}) melebihi qty yang tersedia ({$availableQtyFormatted}). Silakan kurangi qty.")
                ->danger()
                ->duration(7000)
                ->send();
            $this->halt();
        }

         // Check if the same record exists
        $exists = DeliveryOrder::where('sp3m_id', $sp3mId)
            ->where('tbbm_id', $tbbmId)
            ->where('tahun_anggaran', $tahunAnggaran)
            ->where('do_id', '!=', $id)
            ->exists();

        if ($exists) {
            // Show Filament error notification
            $dataSp3m = Sp3m::find($sp3mId);
            $dataTbbm = Tbbm::find($tbbmId);

            $message = 'Nomor SP3M "'.ucwords($dataSp3m->nomor_sp3m).'", TBBM "'.ucwords($dataTbbm->depot).'" dan Tahun Anggaran "'.ucwords($tahunAnggaran).'" sudah ada';

            Notification::make()
                ->title('Error!')
                ->body($message)
                ->danger()
                ->send();

            // Prevent form submission
            $this->halt();
        }

        // Validasi kapasitas alpal (rob + qtyDiff tidak boleh melebihi kapasitas)
        if ($sp3m->alpal) {
            $alpal = $sp3m->alpal;
            $newRob = $alpal->rob + $qtyDiff;
            
            if ($newRob > $alpal->kapasitas) {
                $newQtyFormatted = number_format($newQty, 0, ',', '.');
                $robFormatted = number_format($alpal->rob, 0, ',', '.');
                $kapasitasFormatted = number_format($alpal->kapasitas, 0, ',', '.');
                $sisaKapasitas = $alpal->kapasitas - $alpal->rob;
                $sisaKapasitasFormatted = number_format($sisaKapasitas, 0, ',', '.');
                
                Notification::make()
                    ->title('Gagal Mengubah Delivery Order!')
                    ->body("Qty baru ({$newQtyFormatted}) melebihi sisa kapasitas alpal. ROB saat ini: {$robFormatted}, Kapasitas: {$kapasitasFormatted}, Sisa kapasitas: {$sisaKapasitasFormatted}.")
                    ->danger()
                    ->duration(7000)
                    ->send();
                $this->halt();
            }
            
            if ($newRob < 0) {
                Notification::make()
                    ->title('Gagal Mengubah Delivery Order!')
                    ->body("ROB tidak boleh negatif.")
                    ->danger()
                    ->duration(5000)
                    ->send();
                $this->halt();
            }
            
            // Update rob di alpal
            $alpal->rob = $newRob;
            $alpal->save();
        }

        // Update sisa_qty di SP3M
        // Kembalikan qty lama, lalu kurangi dengan qty baru
        $sp3m->sisa_qty = $sp3m->sisa_qty - $qtyDiff;
        $sp3m->save();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->before(function () {
                    // Kembalikan sisa_qty ke SP3M saat delete
                    $sp3m = Sp3m::with('alpal')->find($this->record->sp3m_id);
                    if ($sp3m) {
                        $sp3m->sisa_qty += $this->record->qty;
                        $sp3m->save();
                        
                        // Kurangi rob di alpal
                        if ($sp3m->alpal) {
                            $sp3m->alpal->rob -= $this->record->qty;
                            $sp3m->alpal->save();
                        }
                    }
                }),
            Actions\ForceDeleteAction::make()
                ->label('Hapus Permanen')
                ->before(function () {
                    // Kembalikan sisa_qty ke SP3M saat force delete
                    $sp3m = Sp3m::with('alpal')->find($this->record->sp3m_id);
                    if ($sp3m) {
                        $sp3m->sisa_qty += $this->record->qty;
                        $sp3m->save();
                        
                        // Kurangi rob di alpal
                        if ($sp3m->alpal) {
                            $sp3m->alpal->rob -= $this->record->qty;
                            $sp3m->alpal->save();
                        }
                    }
                }),
            Actions\RestoreAction::make()
                ->label('Pulihkan')
                ->after(function () {
                    // Kurangi sisa_qty dari SP3M saat restore
                    $sp3m = Sp3m::with('alpal')->find($this->record->sp3m_id);
                    if ($sp3m) {
                        $sp3m->sisa_qty -= $this->record->qty;
                        $sp3m->save();
                        
                        // Tambah rob di alpal
                        if ($sp3m->alpal) {
                            $sp3m->alpal->rob += $this->record->qty;
                            $sp3m->alpal->save();
                        }
                    }
                }),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data delivery order berhasil diperbarui.');
    }
    
    public function getTitle(): string
    {
        return 'Ubah Delivery Order';
    }
}
