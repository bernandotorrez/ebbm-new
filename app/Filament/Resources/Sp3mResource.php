<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Sp3mResource\Pages;
use App\Filament\Resources\Sp3mResource\RelationManagers;
use App\Models\Sp3m;
use App\Models\Alpal;
use App\Models\KantorSar;
use App\Models\HargaBekal;
use App\Models\DeliveryOrder;
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
use Filament\Notifications\Notification;

class Sp3mResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = Sp3m::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'SP3M';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'sp3m';

    public static function getModelLabel(): string
    {
        return 'SP3M'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar SP3M';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('has_delivery_order'),
                
                // 1. Tahun Anggaran (TA)
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
                    ->disabled(fn ($record) => $record !== null)
                    ->afterStateHydrated(function (callable $set, $state, $record) {
                        // Simpan tahun anggaran original saat edit
                        if ($record && $state) {
                            $set('original_tahun_anggaran', $state);
                        }
                    })
                    ->afterStateUpdated(function (callable $set, callable $get, $state, $livewire) {
                        // Trigger regenerate nomor SP3M jika tahun anggaran berubah
                        $alpalId = $get('alpal_id');
                        if ($alpalId && $state) {
                            $isEdit = isset($livewire->record) && $livewire->record;
                            $originalTahunAnggaran = $get('original_tahun_anggaran');
                            $originalAlpalId = $get('original_alpal_id');
                            
                            // Cek apakah tahun anggaran dan alut kembali ke original
                            if ($isEdit && $originalTahunAnggaran && $originalAlpalId && 
                                $originalTahunAnggaran == $state && $originalAlpalId == $alpalId) {
                                // Kembalikan ke nomor original
                                $originalNomorSp3m = $get('original_nomor_sp3m');
                                if ($originalNomorSp3m) {
                                    $set('nomor_sp3m_preview', $originalNomorSp3m);
                                    $set('nomor_sp3m_changed', false);
                                }
                            } else {
                                // Generate ulang nomor SP3M
                                $alpal = Alpal::find($alpalId);
                                if ($alpal) {
                                    $kodeAlut = $alpal->kode_alut ?? '000';
                                    $bulanRomawi = static::getBulanRomawi(now()->month);
                                    $tahun = $state; // Gunakan tahun_anggaran
                                    $pattern = "SP3M.{$kodeAlut}/{$bulanRomawi}/SAR-{$tahun}";
                                    
                                    // Include soft deleted records untuk menghindari duplicate nomor
                                    $lastSp3m = Sp3m::withTrashed()
                                        ->where('nomor_sp3m', 'like', "%{$pattern}")
                                        ->orderBy('nomor_sp3m', 'desc')
                                        ->first();
                                    
                                    $sequence = 1;
                                    if ($lastSp3m) {
                                        preg_match('/^(\d{4})\//', $lastSp3m->nomor_sp3m, $matches);
                                        if (isset($matches[1])) {
                                            $sequence = intval($matches[1]) + 1;
                                        }
                                    }
                                    
                                    $nomorSp3m = sprintf('%04d/%s', $sequence, $pattern);
                                    $set('nomor_sp3m_preview', $nomorSp3m);
                                    $set('nomor_sp3m_changed', true);
                                }
                            }
                        }
                    }),
                
                // 2. TW
                Forms\Components\Select::make('tw')
                    ->label('Triwulan')
                    ->required()
                    ->options([
                        '1' => 'Triwulan I',
                        '2' => 'Triwulan II',
                        '3' => 'Triwulan III',
                        '4' => 'Triwulan IV',
                    ])
                    ->searchable()
                    ->live()
                    ->disabled(fn ($record) => $record !== null),
                
                // 3. Alut
                Forms\Components\Select::make('alpal_id')
                    ->label('Alut')
                    ->required()
                    ->relationship('alpal', 'alpal', function ($query) {
                        $user = Auth::user();
                        // Filter Alut berdasarkan kantor_sar_id user yang login (kecuali Admin dan Kanpus)
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
                    ->disabled(fn ($record) => $record !== null)
                    ->afterStateHydrated(function (callable $set, callable $get, $state, $record) {
                        // Set kantor_sar_info saat form di-load (untuk edit)
                        if ($state) {
                            $alpal = Alpal::find($state);
                            if ($alpal && $alpal->kantor_sar_id) {
                                $kantorSar = KantorSar::find($alpal->kantor_sar_id);
                                $set('kantor_sar_info', $kantorSar ? $kantorSar->kantor_sar : '');
                            }
                            
                            // Jika edit, tampilkan nomor SP3M dari database dan simpan sebagai original
                            if ($record && $record->nomor_sp3m) {
                                $set('nomor_sp3m_preview', $record->nomor_sp3m);
                                $set('original_alpal_id', $state); // Simpan alpal_id original
                                $set('original_nomor_sp3m', $record->nomor_sp3m); // Simpan nomor SP3M original
                            }
                        }
                    })
                    ->afterStateUpdated(function (callable $set, callable $get, $state, $livewire) {
                        if ($state) {
                            $alpal = Alpal::find($state);
                            if ($alpal && $alpal->kantor_sar_id) {
                                $kantorSar = KantorSar::find($alpal->kantor_sar_id);
                                $set('kantor_sar_info', $kantorSar ? $kantorSar->kantor_sar : '');
                                $set('kantor_sar_id', $alpal->kantor_sar_id);
                                
                                // Cek apakah ini mode edit atau create
                                $isEdit = isset($livewire->record) && $livewire->record;
                                
                                // Ambil tahun anggaran
                                $tahunAnggaran = $get('tahun_anggaran');
                                
                                // Cek apakah alut dan tahun anggaran berubah
                                $originalAlpalId = $get('original_alpal_id');
                                $originalTahunAnggaran = $get('original_tahun_anggaran');
                                $originalNomorSp3m = $get('original_nomor_sp3m');
                                
                                // Jika alut dan tahun anggaran kembali ke original, kembalikan nomor SP3M original
                                if ($isEdit && $originalAlpalId && $originalTahunAnggaran && 
                                    $originalAlpalId == $state && $originalTahunAnggaran == $tahunAnggaran && 
                                    $originalNomorSp3m) {
                                    $set('nomor_sp3m_preview', $originalNomorSp3m);
                                    $set('nomor_sp3m_changed', false);
                                } else {
                                    // Alut atau tahun anggaran berubah, atau mode create
                                    $isAlutChanged = $isEdit && $originalAlpalId && $originalAlpalId != $state;
                                    $isTahunChanged = $isEdit && $originalTahunAnggaran && $originalTahunAnggaran != $tahunAnggaran;
                                    
                                    // Generate nomor SP3M preview jika create atau ada perubahan
                                    if (!$isEdit || $isAlutChanged || $isTahunChanged) {
                                        if ($tahunAnggaran) {
                                            $kodeAlut = $alpal->kode_alut ?? '000';
                                            $bulanRomawi = static::getBulanRomawi(now()->month);
                                            $tahun = $tahunAnggaran; // Gunakan tahun_anggaran
                                            $pattern = "SP3M.{$kodeAlut}/{$bulanRomawi}/SAR-{$tahun}";
                                            
                                            // Get next sequence - Include soft deleted records
                                            $lastSp3m = Sp3m::withTrashed()
                                                ->where('nomor_sp3m', 'like', "%{$pattern}")
                                                ->orderBy('nomor_sp3m', 'desc')
                                                ->first();
                                            
                                            $sequence = 1;
                                            if ($lastSp3m) {
                                                // Extract sequence from nomor_sp3m (format: 0001/SP3M.026/XII/SAR-2025)
                                                preg_match('/^(\d{4})\//', $lastSp3m->nomor_sp3m, $matches);
                                                if (isset($matches[1])) {
                                                    $sequence = intval($matches[1]) + 1;
                                                }
                                            }
                                            
                                            $nomorSp3m = sprintf('%04d/%s', $sequence, $pattern);
                                            $set('nomor_sp3m_preview', $nomorSp3m);
                                            $set('nomor_sp3m_changed', true); // Flag bahwa nomor berubah
                                        }
                                    }
                                }
                            }
                        } else {
                            $set('kantor_sar_info', '');
                            $set('kantor_sar_id', null);
                            $set('nomor_sp3m_preview', '');
                        }
                        
                        // Reset TBBM/DPPU ketika Alut berubah (kecuali saat edit dan tidak berubah)
                        $isEdit = isset($livewire->record) && $livewire->record;
                        $originalAlpalId = $get('original_alpal_id');
                        
                        if (!$isEdit || ($originalAlpalId && $originalAlpalId != $state)) {
                            $set('tbbm_id', null);
                        }
                    }),
                
                // 4. Nomor SP3M (Auto-generated preview)
                Forms\Components\TextInput::make('nomor_sp3m_preview')
                    ->label('Nomor SP3M')
                    ->placeholder('Pilih Alut & Tahun Anggaran untuk generate nomor')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText(fn ($record) => $record 
                        ? 'Nomor akan di-generate ulang jika Alut berubah' 
                        : 'Nomor akan di-generate otomatis saat menyimpan')
                    ->extraAttributes([
                        'class' => 'dark:bg-gray-800 dark:text-gray-400 bg-gray-100 text-gray-600',
                    ]),
                
                Forms\Components\Hidden::make('nomor_sp3m'),
                Forms\Components\Hidden::make('original_alpal_id'),
                Forms\Components\Hidden::make('original_tahun_anggaran'),
                Forms\Components\Hidden::make('original_nomor_sp3m'),
                Forms\Components\Hidden::make('nomor_sp3m_changed'),
                
                // 5. Tanggal SP3M
                Forms\Components\DatePicker::make('tanggal_sp3m')
                    ->label('Tanggal SP3M')
                    ->placeholder('Tanggal SP3M')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->closeOnDateSelection(true)
                    ->validationMessages([
                        'required' => 'Tanggal SP3M harus diisi',
                    ])
                    ->disabled(fn ($record) => $record !== null),
                
                // 6. Kantor SAR (readonly, auto-filled from Alut)
                Forms\Components\TextInput::make('kantor_sar_info')
                    ->label('Kantor SAR')
                    ->disabled()
                    ->dehydrated(false)
                    ->extraAttributes([
                        'class' => 'dark:bg-gray-800 dark:text-gray-400 bg-gray-100 text-gray-600',
                    ]),
                
                Forms\Components\Hidden::make('kantor_sar_id'),
                
                // 7. Jenis Bahan Bakar
                Forms\Components\Select::make('bekal_id')
                    ->label('Jenis Bahan Bakar')
                    ->required()
                    ->relationship('bekal', 'bekal')
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Pilih Jenis Bahan Bakar',
                    ])
                    ->live()
                    ->disabled(fn ($record) => $record !== null),
                
                // 7b. TBBM/DPPU
                Forms\Components\Select::make('tbbm_id')
                    ->label('TBBM/DPPU')
                    ->required()
                    ->relationship('tbbm', 'depot', function ($query, callable $get) {
                        $alpalId = $get('alpal_id');
                        
                        if ($alpalId) {
                            // Ambil kota_id dari Alut -> Kantor SAR -> Kota
                            $alpal = Alpal::with('kantorSar.kota')->find($alpalId);
                            $kotaId = $alpal?->kantorSar?->kota_id;
                            
                            if ($kotaId) {
                                // Filter TBBM berdasarkan kota_id
                                $query->where('kota_id', $kotaId);
                            }
                        }
                        
                        return $query;
                    })
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Pilih TBBM/DPPU',
                    ])
                    ->placeholder('Pilih TBBM/DPPU')
                    ->helperText(fn (callable $get) => $get('alpal_id') 
                        ? 'TBBM/DPPU difilter berdasarkan kota dari Kantor SAR' 
                        : 'Pilih Alut terlebih dahulu untuk memfilter TBBM/DPPU')
                    ->disabled(fn (callable $get) => !$get('alpal_id'))
                    ->live(),
                
                // 8. Qty
                Forms\Components\TextInput::make('qty')
                    ->required()
                    ->label('Qty')
                    ->inputMode('numeric')
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                    ])
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->minValue(1)
                    ->maxLength(10)
                    ->rules(['min:1'])
                    ->validationMessages([
                        'required' => 'Qty harus diisi',
                        'min' => 'Qty minimal 1',
                    ])
                    ->live(debounce: 500)
                    ->afterStateUpdated(function (callable $get, callable $set, $state, $context) {
                        // Only update sisa_qty in create form
                        if ($context === 'create') {
                            $cleanQty = (int) str_replace(['.', ',', ' '], '', $state ?? '0');
                            $set('sisa_qty', $cleanQty ? number_format($cleanQty, 0, ',', '.') : null);
                        }
                    })
                    ->disabled(fn (callable $get) => $get('has_delivery_order') === true)
                    ->helperText(function (callable $get, $record) {
                        if ($record && $get('has_delivery_order') === true) {
                            return '⚠️ SP3M ini sudah memiliki Delivery Order yang berjalan. Qty tidak dapat diubah.';
                        }
                        return null;
                    }),
                
                // 9. Sisa SP3M (readonly, calculated)
                Forms\Components\TextInput::make('sisa_qty')
                    ->label('Sisa SP3M')
                    ->inputMode('numeric')
                    ->disabled()
                    ->dehydrated()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->helperText('Sisa SP3M akan sama dengan Qty saat pertama kali dibuat')
                    ->extraAttributes([
                        'class' => 'dark:bg-gray-800 dark:text-gray-400 bg-gray-100 text-gray-600',
                    ]),
                
                // 10. Lampiran (Multiple - Minimal 1)
                Forms\Components\Repeater::make('lampiran')
                    ->label('Lampiran')
                    ->schema([
                        Forms\Components\TextInput::make('nama_file')
                            ->label('Nama File')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: SP3M, Surat Persetujuan, Dokumen Pendukung')
                            ->validationMessages([
                                'required' => 'Nama file harus diisi',
                            ]),
                        Forms\Components\FileUpload::make('file_path')
                            ->label('File')
                            ->required()
                            ->disk('public')
                            ->directory('sp3m/lampiran')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])
                            ->maxSize(1024)
                            ->helperText('Format: PDF, JPG, JPEG, PNG, GIF, WebP | Maksimal: 1 MB')
                            ->validationMessages([
                                'required' => 'File harus diunggah',
                                'mimes' => 'File harus berupa PDF atau gambar (JPG, JPEG, PNG, GIF, WebP)',
                                'max' => 'Ukuran file maksimal 1 MB',
                            ])
                            ->uploadingMessage('Mengunggah...')
                            ->downloadable()
                            ->openable()
                            ->previewable(),
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->maxLength(500)
                            ->rows(2)
                            ->placeholder('Keterangan tambahan (opsional)'),
                    ])
                    ->columns(1)
                    ->defaultItems(1)
                    ->minItems(1)
                    ->required()
                    ->addActionLabel('+ Tambah Lampiran')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['nama_file'] ?? 'Lampiran Baru')
                    ->reorderable(false)
                    ->cloneable()
                    ->validationMessages([
                        'required' => 'Minimal 1 lampiran harus diisi',
                        'min' => 'Minimal 1 lampiran harus diisi',
                    ])
                    ->helperText('Minimal 1 lampiran harus diisi. Anda dapat menambahkan lebih banyak lampiran dengan klik tombol "+ Tambah Lampiran".')
                    ->visible(fn ($livewire) => $livewire instanceof \App\Filament\Resources\Sp3mResource\Pages\CreateSp3m),
                
                // Section Lampiran untuk halaman edit
                Forms\Components\Section::make('Lampiran')
                    ->schema([
                        Forms\Components\Placeholder::make('lampiran_list')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record || !$record->lampiran || $record->lampiran->count() === 0) {
                                    return new \Illuminate\Support\HtmlString('
                                        <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                            <p>Belum ada lampiran. Gunakan tab "Lampiran" di bawah untuk menambahkan.</p>
                                        </div>
                                    ');
                                }
                                
                                return new \Illuminate\Support\HtmlString(
                                    view('filament.components.sp3m-lampiran-preview', ['lampiran' => $record->lampiran])->render()
                                );
                            }),
                    ])
                    ->visible(fn ($livewire) => $livewire instanceof \App\Filament\Resources\Sp3mResource\Pages\EditSp3m)
                    ->collapsible(),
            ]);
    }

    protected static function getKantorSarOptions(): array
    {
        $user = Auth::user();

        // If user is admin, show all Kantor SAR
        if ($user && $user->level->value === LevelUser::ADMIN->value) {
            return KantorSar::pluck('kantor_sar', 'kantor_sar_id')->toArray();
        }

        // For non-admin users, only show their assigned Kantor SAR
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

    public static function generateNomorSp3m(int $alpalId, ?int $tahunAnggaran = null): string
    {
        $alpal = Alpal::find($alpalId);
        $kodeAlut = $alpal?->kode_alut ?? '000';
        $bulanRomawi = static::getBulanRomawi(now()->month);
        $tahun = $tahunAnggaran ?? now()->year; // Gunakan tahun_anggaran jika ada
        $pattern = "SP3M.{$kodeAlut}/{$bulanRomawi}/SAR-{$tahun}";
        
        // Get next sequence with lock to prevent duplicate - Include soft deleted records
        $lastSp3m = Sp3m::withTrashed()
            ->where('nomor_sp3m', 'like', "%{$pattern}")
            ->lockForUpdate()
            ->orderBy('nomor_sp3m', 'desc')
            ->first();
        
        $sequence = 1;
        if ($lastSp3m) {
            preg_match('/^(\d{4})\//', $lastSp3m->nomor_sp3m, $matches);
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
                Tables\Columns\TextColumn::make('alpal.alpal')
                    ->numeric()
                    ->label('Alut')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kantorSar.kantor_sar')
                    ->numeric()
                    ->label('Kantor Sar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bekal.bekal')
                    ->numeric()
                    ->label('Jenis Bahan Bakar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tbbm.depot')
                    ->label('Depot')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_sp3m')
                    ->label('Nomor SP3M')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_sp3m')
                    ->label('Tanggal SP3M')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tw')
                    ->label('Triwulan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sisa_qty')
                    ->label('Sisa Qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lampiran_count')
                    ->label('Lampiran')
                    ->counts('lampiran')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "{$state} file" : 'Tidak ada')
                    ->sortable()
                    ->action(
                        Tables\Actions\Action::make('viewLampiran')
                            ->label('Lihat Lampiran')
                            ->modalHeading('Daftar Lampiran')
                            ->modalContent(fn ($record) => view('filament.modals.sp3m-lampiran-list', ['lampiran' => $record->lampiran]))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                            ->slideOver()
                    ),
                Tables\Columns\TextColumn::make('harga_satuan')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->sortable()
                    ->visible(fn () => !in_array(Auth::user()?->level?->value, [LevelUser::KANSAR->value, LevelUser::ABK->value])),
                Tables\Columns\TextColumn::make('jumlah_harga')
                    ->label('Jumlah Harga')
                    ->numeric()
                    ->sortable()
                    ->visible(fn () => !in_array(Auth::user()?->level?->value, [LevelUser::KANSAR->value, LevelUser::ABK->value])),
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
                SelectFilter::make('alpal_id')
                    ->label('Alut')
                    ->relationship('alpal', 'alpal')
                    ->preload(),
                SelectFilter::make('bekal_id')
                    ->label('Jenis Bahan Bakar')
                    ->relationship('bekal', 'bekal')
                    ->preload(),
                SelectFilter::make('tahun_anggaran')
                    ->label('Tahun Anggaran'),
                // Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn ($record) => route('export.sp3m-pdf', $record->sp3m_id))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(function ($record) {
                        // Hide delete button if SP3M has DO
                        return !\App\Models\DeliveryOrder::where('sp3m_id', $record->sp3m_id)->exists();
                    })
                    ->modalHeading('Konfirmasi Hapus Data')
                    ->modalSubheading('Apakah kamu yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalButton('Ya, Hapus Sekarang'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->modalHeading('Konfirmasi Hapus Data')
                        ->modalSubheading('Apakah kamu yakin ingin menghapus data yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalButton('Ya, Hapus Sekarang')
                        ->before(function ($records) {
                            // Check if any of the selected records has DO
                            foreach ($records as $record) {
                                $hasDo = \App\Models\DeliveryOrder::where('sp3m_id', $record->sp3m_id)->exists();
                                if ($hasDo) {
                                    Notification::make()
                                        ->title('Gagal Menghapus!')
                                        ->body("SP3M dengan nomor {$record->nomor_sp3m} tidak dapat dihapus karena sudah memiliki Delivery Order.")
                                        ->danger()
                                        ->duration(7000)
                                        ->send();
                                    return false;
                                }
                            }
                        }),
                ])
                ->label('Hapus'),
            ])
            ->recordUrl(null);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LampiranRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSp3ms::route('/'),
            'create' => Pages\CreateSp3m::route('/create'),
            'view' => Pages\ViewSp3m::route('/{record}'),
            'edit' => Pages\EditSp3m::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        // Apply user-level filtering for non-admin and non-kanpus users
        if ($user && 
            $user->level->value !== LevelUser::ADMIN->value && 
            $user->level->value !== LevelUser::KANPUS->value && 
            $user->kantor_sar_id) {
            $query->where('kantor_sar_id', $user->kantor_sar_id);
        }

        return $query;
    }
}
