<?php

namespace App\Filament\Resources\PaguResource\Pages;

use App\Filament\Resources\PaguResource;
use App\Models\GolonganBbm;
use App\Models\Pagu;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPagu extends EditRecord
{
    protected static string $resource = PaguResource::class;

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
        // Apply formatting to fields before saving
        $data['dasar'] = ucwords($data['dasar']);
        $data['nilai_pagu'] = (int) preg_replace('/[^\d]/', '', $data['nilai_pagu']);

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
        $id = $this->data['pagu_id'] ?? null;
        $golonganBbmId = $this->data['golongan_bbm_id'] ?? null;
        $tahunAnggaran = $this->data['tahun_anggaran'] ?? null;

        // Check if the same record exists
        $exists = Pagu::where('golongan_bbm_id', $golonganBbmId)
            ->where('tahun_anggaran', $tahunAnggaran)
            ->where('pagu_id', '!=', $id)
            ->exists();

        if ($exists) {
            // Show Filament error notification
            $dataGolonganBbm = GolonganBbm::find($golonganBbmId);

            $message = 'Golongan BBM "'.ucwords($dataGolonganBbm->golongan).'" dan Tahun Anggaran "'.ucwords($tahunAnggaran).'" sudah ada';

            Notification::make()
                ->title('Error!')
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
            ->body('Data pagu berhasil diperbarui.');
    }
}
