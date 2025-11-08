<?php

namespace App\Filament\Resources\PelumasResource\Pages;

use App\Filament\Resources\PelumasResource;
use App\Models\Pelumas;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePelumas extends CreateRecord
{
    protected static string $resource = PelumasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function beforeCreate(): void
    {
        // Get input values
        $namaPelumas = $this->data['nama_pelumas'] ?? null;
        $packId = $this->data['pack_id'] ?? null;
        $kemasanId = $this->data['kemasan_id'] ?? null;
        $tahun = $this->data['tahun'] ?? null;

        // Check if the same record exists
        $exists = Pelumas::where('nama_pelumas', $namaPelumas)
            ->where('pack_id', $packId)
            ->where('kemasan_id', $kemasanId)
            ->where('tahun', $tahun)
            ->exists();

        if ($exists) {
            // Show Filament error notification
            Notification::make()
                ->title('Error!')
                ->body('Pelumas "'.$namaPelumas.'" dengan pack, kemasan, dan tahun yang sama sudah ada')
                ->danger()
                ->send();

            // Prevent form submission
            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data pelumas berhasil ditambahkan.');
    }

    public function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Buat'),
            $this->getCreateAnotherFormAction()
                ->label('Buat & Buat lainnya'),
            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'Buat Pelumas';
    }
}