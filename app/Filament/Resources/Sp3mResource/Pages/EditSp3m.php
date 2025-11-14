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
        // Apply ucwords() to the 'bekal' field before saving
        $data['qty'] = (int) preg_replace('/[^\d]/', '', $data['qty']);
        $data['harga_satuan'] = (int) preg_replace('/[^\d]/', '', $data['harga_satuan']);
        $data['jumlah_harga'] = (int) preg_replace('/[^\d]/', '', $data['jumlah_harga']);

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

        // Check if the same record exists
        $exists = Sp3m::where('kantor_sar_id', $kantorSarId)
            ->where('alpal_id', $alpalId)
            ->where('bekal_id', $bekalId)
            ->where('tahun_anggaran', $tahunAnggaran)
            ->where('sp3m_id', '!=', $id)
            ->exists();

        if ($exists) {
            // Show Filament error notification
            $dataKantorSar = KantorSar::find($kantorSarId);
            $dataAlpal = Alpal::find($alpalId);
            $dataBekal = Bekal::find($bekalId);

            $message = 'Kantor SAR "'.ucwords($dataKantorSar->kantor_sar).'", Alpal "'.ucwords($dataAlpal->alpal).'", Bekal "'.ucwords($dataBekal->bekal).'" dan Tahun Anggaran "'.ucwords($tahunAnggaran).'" sudah ada';

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
            ->body('Data SP3M berhasil diperbarui.');
    }

    public function getTitle(): string
    {
        return 'Ubah SP3M';
    }
}
