<?php

namespace App\Filament\Resources\Sp3mResource\Pages;

use App\Filament\Resources\Sp3mResource;
use App\Models\DeliveryOrder;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Illuminate\Support\Facades\Storage;

class ViewSp3m extends ViewRecord
{
    protected static string $resource = Sp3mResource::class;

    public function getTitle(): string
    {
        return 'Lihat SP3M';
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Informasi SP3M')
                    ->schema([
                        Components\TextEntry::make('nomor_sp3m')
                            ->label('Nomor SP3M'),
                        Components\TextEntry::make('tanggal_sp3m')
                            ->label('Tanggal SP3M')
                            ->date('d/m/Y'),
                        Components\TextEntry::make('tahun_anggaran')
                            ->label('Tahun Anggaran'),
                        Components\TextEntry::make('tw')
                            ->label('Triwulan')
                            ->formatStateUsing(fn ($state) => "Triwulan {$state}"),
                        Components\TextEntry::make('alpal.alpal')
                            ->label('Alut'),
                        Components\TextEntry::make('kantorSar.kantor_sar')
                            ->label('Kantor SAR'),
                        Components\TextEntry::make('bekal.bekal')
                            ->label('Jenis Bahan Bakar'),
                        Components\TextEntry::make('tbbm.depot')
                            ->label('TBBM/DPPU'),
                        Components\TextEntry::make('qty')
                            ->label('Qty')
                            ->numeric(),
                        Components\TextEntry::make('sisa_qty')
                            ->label('Sisa Qty')
                            ->numeric(),
                    ])
                    ->columns(2),
                
                Components\Section::make('Lampiran')
                    ->schema([
                        Components\RepeatableEntry::make('lampiran')
                            ->label('')
                            ->schema([
                                Components\TextEntry::make('nama_file')
                                    ->label('Nama File')
                                    ->weight('bold'),
                                Components\TextEntry::make('keterangan')
                                    ->label('Keterangan')
                                    ->placeholder('Tidak ada keterangan'),
                                Components\TextEntry::make('file_path')
                                    ->label('File')
                                    ->formatStateUsing(fn ($state) => basename($state))
                                    ->url(fn ($record) => route('preview.sp3m-lampiran', $record->lampiran_id))
                                    ->openUrlInNewTab()
                                    ->icon('heroicon-o-document')
                                    ->iconColor('primary'),
                                Components\TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime('d/m/Y H:i'),
                            ])
                            ->columns(2)
                            ->contained(false)
                            ->grid(1),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
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
