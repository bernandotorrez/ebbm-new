<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use App\Models\DeliveryOrder;
use App\Models\Sp3m;
use App\Models\Tbbm;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryOrder extends CreateRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Apply ucwords() to the 'bekal' field before saving
        $data['qty'] = (int) preg_replace('/[^\d]/', '', $data['qty']);
        $data['harga_satuan'] = (int) preg_replace('/[^\d]/', '', $data['harga_satuan']);
        $data['pbbkb'] = (int) number_format($data['pbbkb'], 0, ',', '.');
        $data['jumlah_harga'] = (int) preg_replace('/[^\d]/', '', $data['jumlah_harga']);

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
        $sp3m = Sp3m::find($sp3mId);
        
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

        // Check if the same record exists
        $exists = DeliveryOrder::where('sp3m_id', $sp3mId)
            ->where('tbbm_id', $tbbmId)
            ->where('tahun_anggaran', $tahunAnggaran)
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
