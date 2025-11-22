<?php

namespace App\Filament\Resources\LaporanDetailTagihanResource\Pages;

use App\Filament\Resources\LaporanDetailTagihanResource;
use App\Models\ViewLaporanDetailTagihan;
use App\Models\KantorSar;
use App\Exports\LaporanDetailTagihanExport;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class LaporanDetailTagihan extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = LaporanDetailTagihanResource::class;

    protected static string $view = 'filament.resources.laporan-detail-tagihan-resource.pages.laporan-detail-tagihan';

    public ?string $tanggal_awal = null;
    public ?string $tanggal_akhir = null;
    public ?string $kantor_sar_id = null;
    public bool $showTable = false;

    public function mount(): void
    {
        // Cek akses hanya untuk Kanpus
        $user = auth()->user();
        
        if (!$user || $user->level->value !== \App\Enums\LevelUser::KANPUS->value) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Hanya level Kanpus yang memiliki akses ke Laporan Detail Tagihan.')
                ->danger()
                ->send();
            
            $this->redirect('/admin');
            return;
        }
        
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('tanggal_awal')
                ->label('Tanggal Awal')
                ->required()
                ->maxDate(now()),
            
            DatePicker::make('tanggal_akhir')
                ->label('Tanggal Akhir')
                ->required()
                ->maxDate(now())
                ->afterOrEqual('tanggal_awal'),
            
            Select::make('kantor_sar_id')
                ->label('Kantor SAR')
                ->options(function () {
                    $options = KantorSar::pluck('kantor_sar', 'kantor_sar_id')->toArray();
                    return ['semua' => 'Semua'] + $options;
                })
                ->default('semua')
                ->required()
                ->searchable(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('row_number')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('tanggal_isi')
                    ->label('Tanggal Isi')
                    ->date('d-m-Y')
                    ->sortable(),
                TextColumn::make('nomor_sp3m')
                    ->label('Nomor SP3M')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nomor_do')
                    ->label('Nomor DO')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('qty')
                    ->label('Qty (Liter)')
                    ->numeric(0)
                    ->sortable(),
                TextColumn::make('harga_per_liter')
                    ->label('Harga per Liter (Rp)')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('kantor_sar')
                    ->label('Kantor SAR')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('alpal')
                    ->label('Alut')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jumlah_harga')
                    ->label('Jumlah Harga')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('ppn_11')
                    ->label('PPN (11%)')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('ppkb')
                    ->label('PPKB')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('jumlah_pembulatan')
                    ->label('Pembulatan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_setelah_pembulatan')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('tanggal_isi', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    protected function getTableQuery(): Builder
    {
        $query = ViewLaporanDetailTagihan::query();

        if ($this->showTable && $this->tanggal_awal && $this->tanggal_akhir) {
            // Gunakan whereDate untuk memastikan filter tanggal bekerja dengan benar
            $query->where('tanggal_isi', '>=', $this->tanggal_awal)
                  ->where('tanggal_isi', '<=', $this->tanggal_akhir);

            if ($this->kantor_sar_id && $this->kantor_sar_id !== 'semua') {
                $kantorSar = KantorSar::find($this->kantor_sar_id);
                if ($kantorSar) {
                    $query->where('kantor_sar', $kantorSar->kantor_sar);
                }
            }
        } else {
            // Return empty query if not showing table
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public function tampilkan(): void
    {
        $this->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            'kantor_sar_id' => 'required',
        ]);

        $this->showTable = true;
        
        Notification::make()
            ->title('Data berhasil ditampilkan')
            ->success()
            ->send();
    }

    public function exportExcel()
    {
        if (!$this->showTable || !$this->tanggal_awal || !$this->tanggal_akhir) {
            Notification::make()
                ->title('Silakan tampilkan data terlebih dahulu')
                ->warning()
                ->send();
            return;
        }

        $kantorSarName = 'Semua';
        if ($this->kantor_sar_id && $this->kantor_sar_id !== 'semua') {
            $kantorSar = KantorSar::find($this->kantor_sar_id);
            $kantorSarName = $kantorSar ? $kantorSar->kantor_sar : 'Semua';
        }

        $filename = 'Laporan_Detail_Tagihan_' . 
                    str_replace('-', '', $this->tanggal_awal) . '_' . 
                    str_replace('-', '', $this->tanggal_akhir) . '_' . 
                    str_replace(' ', '_', $kantorSarName) . '.xlsx';

        return Excel::download(
            new LaporanDetailTagihanExport(
                $this->tanggal_awal,
                $this->tanggal_akhir,
                $this->kantor_sar_id
            ),
            $filename
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Laporan Detail Tagihan';
    }
}
