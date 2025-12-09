<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Sp3kResource\Pages;
use App\Models\TxSp3k;
use App\Models\KantorSar;
use App\Models\Pagu;
use App\Enums\LevelUser;
use App\Traits\RoleBasedResourceAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Sp3kResource extends Resource
{
    use RoleBasedResourceAccess;

    protected static ?string $model = TxSp3k::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'SP3K';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'sp3k';

    public static function getModelLabel(): string
    {
        return 'SP3K';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar SP3K';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1. Tahun Anggaran
                Forms\Components\Select::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->required()
                    ->options(function () {
                        return DB::table('tx_pagu')
                            ->select('tahun_anggaran')
                            ->distinct()
                            ->orderBy('tahun_anggaran', 'desc')
                            ->pluck('tahun_anggaran', 'tahun_anggaran')
                            ->toArray();
                    })
                    ->searchable()
                    ->validationMessages([
                        'required' => 'Pilih Tahun Anggaran',
                    ])
                    ->preload()
                    ->live()
                    ->afterStateHydrated(function (callable $set, $state, $record) {
                        if ($record && $state) {
                            $set('original_tahun_anggaran', $state);
                        }
                    })
                    ->afterStateUpdated(function (callable $set, callable $get, $state, $livewire) {
                        $alpalId = $get('alpal_id');
                        if ($alpalId && $state) {
                            $isEdit = isset($livewire->record) && $livewire->record;
                            $originalTahunAnggaran = $get('original_tahun_anggaran');
                            $originalAlpalId = $get('original_alpal_id');
                            
                            if ($isEdit && $originalTahunAnggaran && $originalAlpalId && 
                                $originalTahunAnggaran == $state && $originalAlpalId == $alpalId) {
                                $originalNomorSp3k = $get('original_nomor_sp3k');
                                if ($originalNomorSp3k) {
                                    $set('nomor_sp3k_preview', $originalNomorSp3k);
                                    $set('nomor_sp3k_changed', false);
                                }
                            } else {
                                $alpal = \App\Models\Alpal::find($alpalId);
                                if ($alpal) {
                                    $kodeAlut = $alpal->kode_alut ?? '000';
                                    $bulanRomawi = static::getBulanRomawi(now()->month);
                                    $tahun = $state;
                                    $pattern = "SP3K.{$kodeAlut}/{$bulanRomawi}/SAR-{$tahun}";
                                    
                                    $lastSp3k = TxSp3k::where('nomor_sp3k', 'like', "%{$pattern}")
                                        ->orderBy('nomor_sp3k', 'desc')
                                        ->first();
                                    
                                    $sequence = 1;
                                    if ($lastSp3k) {
                                        preg_match('/^(\d{4})\//', $lastSp3k->nomor_sp3k, $matches);
                                        if (isset($matches[1])) {
                                            $sequence = intval($matches[1]) + 1;
                                        }
                                    }
                                    
                                    $nomorSp3k = sprintf('%04d/%s', $sequence, $pattern);
                                    $set('nomor_sp3k_preview', $nomorSp3k);
                                    $set('nomor_sp3k_changed', true);
                                }
                            }
                        }
                    }),
                
                // 2. Alut
                Forms\Components\Select::make('alpal_id')
                    ->label('Alut')
                    ->required()
                    ->relationship('alpal', 'alpal', function ($query) {
                        $user = Auth::user();
                        if ($user && 
                            $user->level->value !== LevelUser::ADMIN->value && 
                            $user->level->value !== LevelUser::KANPUS->value && 
                            $user->kantor_sar_id) {
                            $query->where('kantor_sar_id', $user->kantor_sar_id);
                        }
                        return $query;
                    })
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Pilih Alut',
                    ])
                    ->live()
                    ->afterStateHydrated(function (callable $set, callable $get, $state, $record) {
                        if ($state) {
                            $alpal = \App\Models\Alpal::find($state);
                            if ($alpal && $alpal->kantor_sar_id) {
                                $kantorSar = KantorSar::find($alpal->kantor_sar_id);
                                $set('kantor_sar_info', $kantorSar ? $kantorSar->kantor_sar : '');
                                $set('kantor_sar_id', $alpal->kantor_sar_id);
                            }
                            
                            if ($record && $record->nomor_sp3k) {
                                $set('nomor_sp3k_preview', $record->nomor_sp3k);
                                $set('original_alpal_id', $state);
                                $set('original_nomor_sp3k', $record->nomor_sp3k);
                            }
                        }
                    })
                    ->afterStateUpdated(function (callable $set, callable $get, $state, $livewire) {
                        if ($state) {
                            $alpal = \App\Models\Alpal::find($state);
                            if ($alpal && $alpal->kantor_sar_id) {
                                $kantorSar = KantorSar::find($alpal->kantor_sar_id);
                                $set('kantor_sar_info', $kantorSar ? $kantorSar->kantor_sar : '');
                                $set('kantor_sar_id', $alpal->kantor_sar_id);
                                
                                $isEdit = isset($livewire->record) && $livewire->record;
                                $tahunAnggaran = $get('tahun_anggaran');
                                $originalAlpalId = $get('original_alpal_id');
                                $originalTahunAnggaran = $get('original_tahun_anggaran');
                                $originalNomorSp3k = $get('original_nomor_sp3k');
                                
                                if ($isEdit && $originalAlpalId && $originalTahunAnggaran && 
                                    $originalAlpalId == $state && $originalTahunAnggaran == $tahunAnggaran && 
                                    $originalNomorSp3k) {
                                    $set('nomor_sp3k_preview', $originalNomorSp3k);
                                    $set('nomor_sp3k_changed', false);
                                } else {
                                    $isAlutChanged = $isEdit && $originalAlpalId && $originalAlpalId != $state;
                                    $isTahunChanged = $isEdit && $originalTahunAnggaran && $originalTahunAnggaran != $tahunAnggaran;
                                    
                                    if (!$isEdit || $isAlutChanged || $isTahunChanged) {
                                        if ($tahunAnggaran) {
                                            $kodeAlut = $alpal->kode_alut ?? '000';
                                            $bulanRomawi = static::getBulanRomawi(now()->month);
                                            $tahun = $tahunAnggaran;
                                            $pattern = "SP3K.{$kodeAlut}/{$bulanRomawi}/SAR-{$tahun}";
                                            
                                            $lastSp3k = TxSp3k::where('nomor_sp3k', 'like', "%{$pattern}")
                                                ->orderBy('nomor_sp3k', 'desc')
                                                ->first();
                                            
                                            $sequence = 1;
                                            if ($lastSp3k) {
                                                preg_match('/^(\d{4})\//', $lastSp3k->nomor_sp3k, $matches);
                                                if (isset($matches[1])) {
                                                    $sequence = intval($matches[1]) + 1;
                                                }
                                            }
                                            
                                            $nomorSp3k = sprintf('%04d/%s', $sequence, $pattern);
                                            $set('nomor_sp3k_preview', $nomorSp3k);
                                            $set('nomor_sp3k_changed', true);
                                        }
                                    }
                                }
                            }
                        } else {
                            $set('kantor_sar_info', '');
                            $set('kantor_sar_id', null);
                            $set('nomor_sp3k_preview', '');
                        }
                    }),
                
                // 3. Nomor SP3K (Auto-generated preview)
                Forms\Components\TextInput::make('nomor_sp3k_preview')
                    ->label('Nomor SP3K')
                    ->placeholder('Pilih Alut untuk generate nomor')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText(fn ($record) => $record 
                        ? 'Nomor akan di-generate ulang jika Alut berubah' 
                        : 'Nomor akan di-generate otomatis saat menyimpan')
                    ->extraAttributes([
                        'class' => 'dark:bg-gray-800 dark:text-gray-400 bg-gray-100 text-gray-600',
                    ]),
                
                Forms\Components\Hidden::make('nomor_sp3k'),
                Forms\Components\Hidden::make('original_alpal_id'),
                Forms\Components\Hidden::make('original_tahun_anggaran'),
                Forms\Components\Hidden::make('original_nomor_sp3k'),
                Forms\Components\Hidden::make('nomor_sp3k_changed'),
                
                // 4. Kantor SAR (auto-filled from Alut)
                Forms\Components\Hidden::make('kantor_sar_id'),
                Forms\Components\TextInput::make('kantor_sar_info')
                    ->label('Kantor SAR')
                    ->disabled()
                    ->dehydrated(false)
                    ->extraAttributes([
                        'class' => 'dark:bg-gray-800 dark:text-gray-400 bg-gray-100 text-gray-600',
                    ]),
                Forms\Components\DatePicker::make('tanggal_sp3k')
                    ->label('Tanggal SP3K')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection(true),
                Forms\Components\Select::make('tw')
                    ->label('Triwulan')
                    ->required()
                    ->options([
                        '1' => 'Triwulan I',
                        '2' => 'Triwulan II',
                        '3' => 'Triwulan III',
                        '4' => 'Triwulan IV',
                    ])
                    ->searchable(),
                Forms\Components\Repeater::make('details')
                    ->relationship('details')
                    ->label('Detail SP3K')
                    ->schema([
                        Forms\Components\Select::make('pelumas_id')
                            ->relationship(name: 'pelumas', titleAttribute: 'nama_pelumas')
                            ->label('Pelumas')
                            ->searchable()
                            ->preload()
                            ->validationMessages([
                                'required' => 'Pilih Pelumas',
                            ])
                            ->required()
                            ->live()
                            ->afterStateHydrated(function (callable $get, callable $set, $state) {
                                // Populate pack, kemasan, jumlah_isi saat load data (edit)
                                if ($state) {
                                    $pelumas = \App\Models\Pelumas::with(['pack', 'kemasan'])->find($state);
                                    if ($pelumas) {
                                        $pack = $pelumas->pack;
                                        $kemasan = $pelumas->kemasan;

                                        $set('pack', $pack ? $pack->nama_pack : '');
                                        $set('kemasan_liter', $kemasan ? $kemasan->kemasan_liter : '');
                                        
                                        // Calculate jumlah_isi
                                        $qty = (int) str_replace(['.', ',', ' '], '', $get('qty')) ?: 0;
                                        $kemasanLiter = $kemasan ? $kemasan->kemasan_liter : 0;
                                        $set('jumlah_isi', number_format($qty * $kemasanLiter, 0, ',', '.'));
                                    }
                                }
                            })
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                if ($state) {
                                    $pelumas = \App\Models\Pelumas::with(['pack', 'kemasan'])->find($state);
                                    if ($pelumas) {
                                        $pack = $pelumas->pack;
                                        $kemasan = $pelumas->kemasan;

                                        $set('pack', $pack ? $pack->nama_pack : '');
                                        $set('kemasan_liter', $kemasan ? $kemasan->kemasan_liter : '');
                                        
                                        // Calculate jumlah_isi
                                        $qty = (int) str_replace(['.', ',', ' '], '', $get('qty')) ?: 0;
                                        $kemasanLiter = $kemasan ? $kemasan->kemasan_liter : 0;
                                        $set('jumlah_isi', number_format($qty * $kemasanLiter, 0, ',', '.'));
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('pack')
                            ->label('Pack')
                            ->readOnly()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('kemasan_liter')
                            ->label('Kemasan (Liter)')
                            ->readOnly()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('qty')
                            ->label('Qty')
                            ->required()
                            ->inputMode('numeric')
                            ->extraInputAttributes([
                                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")',
                                'maxlength' => '10'
                            ])
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                            ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                            ->minValue(1)
                            ->live(debounce: 500)
                            ->afterStateHydrated(function (callable $get, callable $set, $state) {
                                // Calculate jumlah_isi saat load data
                                if ($state) {
                                    $qty = (int) str_replace(['.', ',', ' '], '', $state) ?: 0;
                                    $kemasanLiter = (int) str_replace(['.', ',', ' '], '', $get('kemasan_liter')) ?: 0;
                                    $set('jumlah_isi', number_format($qty * $kemasanLiter, 0, ',', '.'));
                                }
                            })
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                // Calculate jumlah_isi saat user ubah qty
                                $qty = (int) str_replace(['.', ',', ' '], '', $get('qty')) ?: 0;
                                $kemasanLiter = (int) str_replace(['.', ',', ' '], '', $get('kemasan_liter')) ?: 0;
                                $set('jumlah_isi', number_format($qty * $kemasanLiter, 0, ',', '.'));
                            }),
                        Forms\Components\TextInput::make('jumlah_isi')
                            ->label('Jumlah Isi (Liter)')
                            ->readOnly()
                            ->dehydrated(false),
                    ])
                    ->columns(5)
                    ->columnSpan('full')
                    ->collapsible()
                    ->collapsed()
                    ->orderColumn('sort')
                    ->addActionLabel('Tambah Detail')
                    ->reorderableWithButtons()
                    ->itemLabel(fn (array $state): ?string => $state['pelumas_id'] ?? null)
                    ->minItems(1)
                    ->live()
                    ->deleteAction(
                        fn ($action) => $action->hidden(fn (array $arguments, Forms\Components\Repeater $component): bool => count($component->getState()) <= 1)
                    ),
            ]);
    }

    protected static function getKantorSarOptions(): array
    {
        $user = Auth::user();

        // Admin dan Kanpus bisa melihat semua Kantor SAR
        if ($user && in_array($user->level->value, [LevelUser::ADMIN->value, LevelUser::KANPUS->value])) {
            return KantorSar::pluck('kantor_sar', 'kantor_sar_id')->toArray();
        }

        // Untuk Kansar dan ABK, hanya tampilkan Kantor SAR mereka
        if ($user && $user->kantor_sar_id) {
            return KantorSar::where('kantor_sar_id', $user->kantor_sar_id)
                ->pluck('kantor_sar', 'kantor_sar_id')
                ->toArray();
        }

        // If no user or no kantor_sar_id assigned, return empty array
        return [];
    }

    protected static function getBulanRomawi(int $bulan): string
    {
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $romawi[$bulan] ?? 'I';
    }

    public static function generateNomorSp3k(int $alpalId, ?int $tahunAnggaran = null): string
    {
        $alpal = \App\Models\Alpal::find($alpalId);
        $kodeAlut = $alpal?->kode_alut ?? '000';
        $bulanRomawi = static::getBulanRomawi(now()->month);
        $tahun = $tahunAnggaran ?? now()->year;
        $pattern = "SP3K.{$kodeAlut}/{$bulanRomawi}/SAR-{$tahun}";
        
        // Get next sequence with lock to prevent duplicate
        $lastSp3k = TxSp3k::where('nomor_sp3k', 'like', "%{$pattern}")
            ->lockForUpdate()
            ->orderBy('nomor_sp3k', 'desc')
            ->first();
        
        $sequence = 1;
        if ($lastSp3k) {
            preg_match('/^(\d{4})\//', $lastSp3k->nomor_sp3k, $matches);
            if (isset($matches[1])) {
                $sequence = intval($matches[1]) + 1;
            }
        }
        
        return sprintf('%04d/%s', $sequence, $pattern);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kantorSar.kantor_sar')
                    ->label('Kantor SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_sp3k')
                    ->label('Nomor SP3K')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_sp3k')
                    ->label('Tanggal SP3K')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tw')
                    ->label('Triwulan')
                    ->searchable()
                    ->sortable(),
                 Tables\Columns\TextColumn::make('pelumas_list')
                    ->label('Pelumas')
                    ->html()
                    ->getStateUsing(function ($record) {
                        // pastikan relasi sudah di-load
                        return $record->details
                            ->map(fn ($detail) => $detail->pelumas?->nama_pelumas)
                            ->filter() // buang null
                            ->map(fn ($nama) => "<p>{$nama}</p>")
                            ->implode('');
                    }),
                Tables\Columns\TextColumn::make('jumlah_qty')
                    ->label('Jumlah Qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_harga')
                    ->label('Jumlah Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kantor_sar_id')
                    ->label('Kantor SAR')
                    ->options(static::getKantorSarOptions())
                    ->preload(),
                SelectFilter::make('tahun_anggaran')
                    ->label('Tahun Anggaran'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->modalHeading('Konfirmasi Hapus Data')
                        ->modalSubheading('Apakah kamu yakin ingin menghapus data yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalButton('Ya, Hapus Sekarang'),
                ])
                ->label('Hapus'),
            ])
            ->recordUrl(null);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSp3ks::route('/'),
            'create' => Pages\CreateSp3k::route('/create'),
            'edit' => Pages\EditSp3k::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['details.pelumas']); // 👈 penting

        $user = Auth::user();
        // Hanya filter untuk Kansar dan ABK, Admin dan Kanpus bisa lihat semua
        if ($user && 
            !in_array($user->level->value, [LevelUser::ADMIN->value, LevelUser::KANPUS->value]) && 
            $user->kantor_sar_id) {
            $query->where('kantor_sar_id', $user->kantor_sar_id);
        }

        return $query;
    }
}
