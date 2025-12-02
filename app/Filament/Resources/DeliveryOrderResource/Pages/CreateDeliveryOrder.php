<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use App\Models\DeliveryOrder;
use App\Models\Sp3m;
use App\Models\Tbbm;
use App\Models\KantorSar;
use App\Models\HargaBekal;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryOrder extends CreateRecord
{
    protected static string $resource = DeliveryOrderResource::class;
    
    public bool $isCreating = false;

    public function mount(): void
    {
        parent::mount();
        
        $user = auth()->user();
        
        // Cek apakah user adalah Admin atau Kanpus
        if ($user && in_array($user->level->value, [\App\Enums\LevelUser::ADMIN->value, \App\Enums\LevelUser::KANPUS->value])) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Admin dan Kanpus tidak memiliki akses untuk membuat Delivery Order.')
                ->danger()
                ->send();
            
            $this->redirect(DeliveryOrderResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Clean numeric fields
        $data['qty'] = (int) preg_replace('/[^\d]/', '', $data['qty']);
        
        // Get bekal_id from SP3M
        $sp3mId = $data['sp3m_id'] ?? null;
        if ($sp3mId) {
            $sp3m = Sp3m::find($sp3mId);
            if ($sp3m) {
                $data['bekal_id'] = $sp3m->bekal_id;
                $data['kantor_sar_id'] = $sp3m->kantor_sar_id;
            }
        }
        
        // Get kota_id from TBBM
        // $tbbmId = $data['tbbm_id'] ?? null;
        // if ($tbbmId) {
        //     $tbbm = Tbbm::find($tbbmId);
        //     if ($tbbm) {
        //         $data['kota_id'] = $tbbm->kota_id;
        //     }
        // }

        // Get Kota ID from Kantor Sar
        $kantorSarId = $data['kantor_sar_id'] ?? null;
        if ($kantorSarId) {
            $kantorSar = KantorSar::find($kantorSarId);
            if ($kantorSar) {
                $data['kota_id'] = $kantorSar->kota_id;
            }
        }
        
        // Auto-fill harga_bekal_id berdasarkan bekal_id dan kota_id
        $bekalId = $data['bekal_id'] ?? null;
        $kotaId = $data['kota_id'] ?? null;
        
        if ($bekalId && $kotaId) {
            // Cari harga bekal terbaru berdasarkan tanggal_update
            $hargaBekal = HargaBekal::where('bekal_id', $bekalId)
                ->where('kota_id', $kotaId)
                ->whereNotNull('tanggal_update')
                ->orderBy('tanggal_update', 'desc')
                ->first();
            
            // Jika ada, set harga_bekal_id, jika tidak ada set null
            $data['harga_bekal_id'] = $hargaBekal ? $hargaBekal->harga_bekal_id : null;
        } else {
            $data['harga_bekal_id'] = null;
        }
        
        // Remove unused fields if any
        unset($data['ppn']);
        unset($data['pbbkb']);
        unset($data['harga_satuan']);

        return $data;
    }
/*
    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }
    */
    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after creation
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        // Set flag to prevent double processing
        if ($this->isCreating) {
            return;
        }
        $this->isCreating = true;
        
        // Get input values
        $sp3mId = $this->data['sp3m_id'] ?? null;
        $qty = (int) preg_replace('/[^\d]/', '', $this->data['qty'] ?? 0);

        // Validasi sisa_qty di SP3M
        $sp3m = Sp3m::with(['alpal', 'bekal', 'kantorSar'])->find($sp3mId);
        
        if (!$sp3m) {
            Notification::make()
                ->title('Gagal Membuat Delivery Order!')
                ->body('SP3M tidak ditemukan.')
                ->danger()
                ->duration(5000)
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

        // Validasi kapasitas alpal (rob + qty tidak boleh melebihi kapasitas)
        if ($sp3m->alpal) {
            $alpal = $sp3m->alpal;
            $newRob = $alpal->rob + $qty;
            
            if ($newRob > $alpal->kapasitas) {
                $qtyFormatted = number_format($qty, 0, ',', '.');
                $robFormatted = number_format($alpal->rob, 0, ',', '.');
                $kapasitasFormatted = number_format($alpal->kapasitas, 0, ',', '.');
                $sisaKapasitas = $alpal->kapasitas - $alpal->rob;
                $sisaKapasitasFormatted = number_format($sisaKapasitas, 0, ',', '.');
                
                Notification::make()
                    ->title('Gagal Membuat Delivery Order!')
                    ->body("Qty DO ({$qtyFormatted}) melebihi sisa kapasitas alpal. ROB saat ini: {$robFormatted}, Kapasitas: {$kapasitasFormatted}, Sisa kapasitas: {$sisaKapasitasFormatted}.")
                    ->danger()
                    ->duration(7000)
                    ->send();
                $this->halt();
            }
        }
    }
    
    protected function afterCreate(): void
    {
        // Get the created record
        $record = $this->record;
        
        // Get SP3M
        $sp3m = Sp3m::with('alpal')->find($record->sp3m_id);
        
        if (!$sp3m) {
            return;
        }
        
        $qty = $record->qty;
        
        // Update dalam transaction untuk memastikan konsistensi
        \DB::transaction(function () use ($sp3m, $qty) {
            // Update sisa_qty di SP3M
            $sp3m->sisa_qty = $sp3m->sisa_qty - $qty;
            $sp3m->save();
            
            // Update rob di alpal jika ada
            if ($sp3m->alpal) {
                $alpal = $sp3m->alpal;
                $alpal->rob = $alpal->rob + $qty;
                $alpal->save();
            }
        });
    }

    protected function getCreatedNotification(): ?Notification
    {
        // Send notification immediately
        Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data delivery order berhasil ditambahkan.')
            ->send();
        
        // Return null to prevent Filament from sending it again
        return null;
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
