<?php

namespace App\Filament\Resources\Sp3mResource\Pages;

use App\Filament\Resources\Sp3mResource;
use App\Models\DeliveryOrder;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSp3m extends ViewRecord
{
    protected static string $resource = Sp3mResource::class;

    public function getTitle(): string
    {
        return 'Lihat SP3M';
    }

    protected function getHeaderActions(): array
    {
        // Check if SP3M has any DO
        $hasDo = DeliveryOrder::where('sp3m_id', $this->record->sp3m_id)->exists();
        
        $actions = [
            Actions\EditAction::make()
                ->label('Ubah'),
        ];

        // If SP3M has no DO, show delete action
        if (!$hasDo) {
            $actions[] = Actions\DeleteAction::make()
                ->label('Hapus')
                ->modalHeading('Konfirmasi Hapus Data')
                ->modalSubheading('Apakah kamu yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalButton('Ya, Hapus Sekarang');
        }
        
        return $actions;
    }
}
