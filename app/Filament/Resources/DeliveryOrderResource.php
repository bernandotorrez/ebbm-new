<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryOrderResource\Pages;
use App\Filament\Resources\DeliveryOrderResource\RelationManagers;
use App\Models\DeliveryOrder;
use App\Models\Sp3m;
use App\Models\KantorSar;
use App\Models\HargaBekal;
use App\Enums\LevelUser;
use App\Traits\RoleBasedResourceAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryOrderResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = DeliveryOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Delivery Order';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'delivery-order';

    public static function getModelLabel(): string
    {
        return 'Delivery Order'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Delivery Order';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        // Kolom Kiri
                        Forms\Components\Select::make('sp3m_id')
                            ->relationship(name: 'sp3m', titleAttribute: 'nomor_sp3m')
                            ->label('Nomor SP3M')
                            ->options(static::getSp3mOptions())
                            ->searchable()
                            ->preload()
                            ->validationMessages([
                                'required' => 'Pilih Nomor SP3M',
                            ])
                            ->required()
                            ->live()
                            ->afterStateHydrated(function (callable $set, $state) {
                                if ($state) {
                                    $sp3m = Sp3m::with(['alpal', 'kantorSar', 'bekal'])->find($state);
                                    if ($sp3m) {
                                        // Set alut
                                        if ($sp3m->alpal_id && $sp3m->alpal) {
                                            $set('alut_info', $sp3m->alpal->alpal);
                                        }
                                        
                                        // Set tahun anggaran
                                        $set('tahun_anggaran', $sp3m->tahun_anggaran);
                                        
                                        // Set kantor sar
                                        if ($sp3m->kantorSar) {
                                            $set('kantor_sar_info', $sp3m->kantorSar->kantor_sar);
                                        }
                                        
                                        // Set jenis bahan bakar
                                        if ($sp3m->bekal) {
                                            $set('jenis_bahan_bakar_info', $sp3m->bekal->bekal);
                                        }
                                        
                                        // Set harga satuan
                                        $set('harga_satuan', $sp3m->harga_satuan);
                                        
                                        // Set sisa qty
                                        $set('sisa_qty_info', number_format($sp3m->sisa_qty, 0, ',', '.'));
                                    }
                                }
                            })
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                if ($state) {
                                    $sp3m = Sp3m::with(['alpal', 'kantorSar', 'bekal'])->find($state);
                                    if ($sp3m) {
                                        // Set alut
                                        if ($sp3m->alpal_id && $sp3m->alpal) {
                                            $set('alut_info', $sp3m->alpal->alpal);
                                        }
                                        
                                        // Set tahun anggaran
                                        $set('tahun_anggaran', $sp3m->tahun_anggaran);
                                        
                                        // Set kantor sar
                                        if ($sp3m->kantorSar) {
                                            $set('kantor_sar_info', $sp3m->kantorSar->kantor_sar);
                                        }
                                        
                                        // Set jenis bahan bakar
                                        if ($sp3m->bekal) {
                                            $set('jenis_bahan_bakar_info', $sp3m->bekal->bekal);
                                        }
                                        
                                        // Set harga satuan
                                        $set('harga_satuan', $sp3m->harga_satuan);
                                        
                                        // Set sisa qty
                                        $set('sisa_qty_info', number_format($sp3m->sisa_qty, 0, ',', '.'));
                                        
                                        // Reset TBBM selection when SP3M changes
                                        $set('tbbm_id', null);
                                    }
                                } else {
                                    $set('alut_info', '');
                                    $set('tahun_anggaran', null);
                                    $set('kantor_sar_info', '');
                                    $set('jenis_bahan_bakar_info', '');
                                    $set('harga_satuan', null);
                                    $set('sisa_qty_info', '');
                                    $set('tbbm_id', null);
                                }
                            }),
                        
                        // Kolom Kanan
                        Forms\Components\TextInput::make('alut_info')
                            ->label('Alut')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('tahun_anggaran')
                            ->label('Tahun Anggaran (TA)')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        
                        Forms\Components\TextInput::make('kantor_sar_info')
                            ->label('Kantor SAR')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('sisa_qty_info')
                            ->label('Sisa Qty SP3M')
                            ->disabled()
                            ->dehydrated(false)
                            ->extraAttributes([
                                'style' => 'font-weight: 600; color: #d97706;'
                            ]),
                        
                        Forms\Components\TextInput::make('jenis_bahan_bakar_info')
                            ->label('Jenis Bahan Bakar')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('nomor_do')
                            ->label('Nomor DO')
                            ->required()
                            ->maxLength(200),
                        
                        Forms\Components\TextInput::make('qty')
                            ->required()
                            ->label('Qty')
                            ->inputMode('numeric')
                            ->afterStateUpdated(function (callable $get, callable $set, $state, $livewire) {
                                $qty = (int) str_replace(['.', ',', ' '], '', $get('qty'));

                                // Validasi qty terhadap sisa_qty SP3M
                                $sp3mId = $get('sp3m_id');
                                if ($sp3mId && $qty > 0) {
                                    $sp3m = Sp3m::find($sp3mId);
                                    if ($sp3m) {
                                        $sisaQty = $sp3m->sisa_qty;
                                        
                                        // Jika sedang edit, tambahkan qty lama ke sisa_qty untuk validasi
                                        if (isset($livewire->record) && $livewire->record->sp3m_id == $sp3mId) {
                                            $sisaQty += $livewire->record->qty;
                                        }
                                        
                                        if ($qty > $sisaQty) {
                                            $set('qty_error', "Qty melebihi sisa qty SP3M (" . number_format($sisaQty, 0, ',', '.') . ")");
                                        } else {
                                            $set('qty_error', null);
                                        }
                                    }
                                }
                            })
                            ->extraInputAttributes([
                                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                            ])
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                            ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                            ->minValue(1)
                            ->maxLength(10)
                            ->live(debounce: 500)
                            ->helperText(fn ($get) => $get('qty_error') ? 
                                new \Illuminate\Support\HtmlString('<span style="color: #ef4444; font-weight: 600;">' . $get('qty_error') . '</span>') 
                                : null
                            )
                            ->rules([
                                'min:1',
                                function ($get, $livewire) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $livewire) {
                                        $qty = (int) str_replace(['.', ',', ' '], '', $value);
                                        $sp3mId = $get('sp3m_id');
                                        
                                        if ($qty < 1) {
                                            $fail("Qty minimal 1.");
                                        }
                                        
                                        if ($sp3mId && $qty > 0) {
                                            $sp3m = Sp3m::find($sp3mId);
                                            if ($sp3m) {
                                                $sisaQty = $sp3m->sisa_qty;
                                                
                                                if (isset($livewire->record) && $livewire->record->sp3m_id == $sp3mId) {
                                                    $sisaQty += $livewire->record->qty;
                                                }
                                                
                                                if ($qty > $sisaQty) {
                                                    $fail("Qty ({$qty}) melebihi sisa qty SP3M ({$sisaQty}).");
                                                }
                                            }
                                        }
                                    };
                                },
                            ]),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_do')
                            ->label('Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection(true),
                        
                        Forms\Components\Select::make('tbbm_id')
                            ->label('TBBM/DPPU')
                            ->searchable()
                            ->preload()
                            ->options(function (callable $get) {
                                $sp3mId = $get('sp3m_id');
                                
                                if (!$sp3mId) {
                                    return [];
                                }
                                
                                // Get SP3M with kantor_sar relationship
                                $sp3m = Sp3m::with('kantorSar')->find($sp3mId);
                                
                                if (!$sp3m || !$sp3m->kantorSar || !$sp3m->kantorSar->kota_id) {
                                    return \App\Models\Tbbm::pluck('depot', 'tbbm_id')->toArray();
                                }
                                
                                // Filter TBBM by kota_id from kantor_sar
                                return \App\Models\Tbbm::where('kota_id', $sp3m->kantorSar->kota_id)
                                    ->pluck('depot', 'tbbm_id')
                                    ->toArray();
                            })
                            ->validationMessages([
                                'required' => 'Pilih TBBM/DDPU',
                            ])
                            ->required(),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\FileUpload::make('file_upload_do')
                            ->label('Lampiran')
                            ->disk('public')
                            ->directory('delivery-order')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->validationMessages([
                                'required' => 'File DO harus diunggah',
                                'file' => 'File DO harus berupa PDF atau gambar',
                                'max' => 'Ukuran file DO maksimal 5MB',
                            ])
                            ->uploadingMessage('Mengunggah...')
                            ->downloadable()
                            ->openable()
                            ->required(),
                        
                        Forms\Components\Placeholder::make('spacer2')
                            ->label('')
                            ->content(''),
                    ]),

            ]);
    }

    protected static function getSp3mOptions(): array
    {
        $user = Auth::user();
        
        // If user is admin, show all SP3M with sisa_qty > 0
        if ($user && $user->level->value === LevelUser::ADMIN->value) {
            return Sp3m::where('sisa_qty', '>', 0)
                ->pluck('nomor_sp3m', 'sp3m_id')
                ->toArray();
        }
        
        // For KANSAR and ABK users, only show SP3M from their assigned Kantor SAR with sisa_qty > 0
        if ($user && $user->kantor_sar_id && 
            in_array($user->level->value, [LevelUser::KANSAR->value, LevelUser::ABK->value])) {
            return Sp3m::where('kantor_sar_id', $user->kantor_sar_id)
                ->where('sisa_qty', '>', 0)
                ->pluck('nomor_sp3m', 'sp3m_id')
                ->toArray();
        }
        
        // For other non-admin users with kantor_sar_id, show SP3M with sisa_qty > 0
        if ($user && $user->kantor_sar_id) {
            return Sp3m::where('kantor_sar_id', $user->kantor_sar_id)
                ->where('sisa_qty', '>', 0)
                ->pluck('nomor_sp3m', 'sp3m_id')
                ->toArray();
        }
        
        // If no user or no kantor_sar_id assigned, return empty array
        return [];
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        
        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user) {
                // Eager load relationships to prevent N+1 queries
                $query->with([
                    'sp3m.alpal',
                    'sp3m.kantorSar',
                    'sp3m.bekal',
                    'tbbm.kota',
                    'bekal',
                    'kota'
                ]);
                
                // Filter khusus untuk ABK - hanya tampilkan DO dari kapal mereka
                if ($user && $user->level->value === LevelUser::ABK->value && $user->alpal_id) {
                    $query->whereHas('sp3m', function ($q) use ($user) {
                        $q->where('alpal_id', $user->alpal_id);
                    });
                }
                // Filter untuk Kansar - tampilkan DO dari kantor SAR mereka
                elseif ($user 
                    && $user->level->value === LevelUser::KANSAR->value
                    && $user->kantor_sar_id) {
                    $query->whereHas('sp3m', function ($q) use ($user) {
                        $q->where('kantor_sar_id', $user->kantor_sar_id);
                    });
                }
                
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('sp3m.nomor_sp3m')
                    ->label('Nomor SP3M')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sp3m.sisa_qty')
                    ->label('Sisa Qty SP3M')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('tbbm.depot')
                    ->label('TBBM/DDPU')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_do')
                    ->label('Tanggal DO')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_do')
                    ->label('Nomor DO/Nota')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sp3m.alpal.alpal')
                    ->label('Kapal/No Reg')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Kuantitas')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_per_liter')
                    ->label('Harga per Liter')
                    ->getStateUsing(function ($record) {
                        $harga = $record->harga; // Using accessor
                        if ($harga == 0) {
                            return 'Belum Update';
                        }
                        return 'Rp ' . number_format($harga, 0, ',', '.');
                    })
                    ->sortable(false)
                    ->visible(fn () => !in_array(Auth::user()?->level?->value, [LevelUser::KANSAR->value, LevelUser::ABK->value])),
                Tables\Columns\TextColumn::make('jumlah_harga_display')
                    ->label('Jumlah Harga')
                    ->getStateUsing(function ($record) {
                        $jumlahHarga = $record->jumlah_harga; // Using accessor
                        return 'Rp ' . number_format($jumlahHarga, 0, ',', '.');
                    })
                    ->sortable(false)
                    ->visible(fn () => !in_array(Auth::user()?->level?->value, [LevelUser::KANSAR->value, LevelUser::ABK->value])),
                Tables\Columns\TextColumn::make('ppn')
                    ->label('PPN')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pbbkb')
                    ->label('PBBKB')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Dihapus Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                // Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('sp3m_id')
                    ->label('SP3M')
                    ->options(static::getSp3mOptions())
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('tbbm_id')
                    ->label('TBBM/DDPU')
                    ->relationship('tbbm', 'depot')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->options(function () {
                        return DB::table('tx_pagu')
                            ->select('tahun_anggaran')
                            ->distinct()
                            ->orderBy('tahun_anggaran', 'desc')
                            ->pluck('tahun_anggaran', 'tahun_anggaran')
                            ->toArray();
                    }),

            ])
            ->actions([
                // Modal edit untuk Kanpus (hanya edit harga BBM)
                Tables\Actions\Action::make('edit_harga_bbm')
                    ->label('Ubah')
                    ->icon('heroicon-o-pencil')
                    ->modalHeading('Ubah Harga BBM')
                    ->modalWidth('md')
                    ->form(function (DeliveryOrder $record) {
                        $record->load(['sp3m.bekal', 'bekal', 'kota', 'tbbm.kota']);
                        
                        // Get bekal_id and kota_id
                        $bekalId = $record->bekal_id ?? $record->sp3m->bekal_id ?? null;
                        $kotaId = $record->kota_id ?? $record->tbbm->kota_id ?? null;
                        
                        return [
                            Forms\Components\TextInput::make('nomor_sp3m')
                                ->label('Nomor SP3M')
                                ->default($record->sp3m->nomor_sp3m ?? '-')
                                ->disabled()
                                ->dehydrated(false),
                            
                            Forms\Components\TextInput::make('nomor_do')
                                ->label('Nomor DO')
                                ->default($record->nomor_do ?? '-')
                                ->disabled()
                                ->dehydrated(false),
                            
                            Forms\Components\Select::make('harga_bekal_id')
                                ->label('Data Harga BBM')
                                ->required()
                                ->options(function () use ($bekalId, $kotaId) {
                                    if (!$bekalId || !$kotaId) {
                                        return [];
                                    }
                                    
                                    $hargaBekals = HargaBekal::with('bekal')
                                        ->where('bekal_id', $bekalId)
                                        ->where('kota_id', $kotaId)
                                        ->orderBy('tanggal_update', 'desc')
                                        ->limit(5)
                                        ->get();
                                    
                                    if ($hargaBekals->isEmpty()) {
                                        return [];
                                    }
                                    
                                    return $hargaBekals->mapWithKeys(function ($item) {
                                        $jenisBekal = $item->bekal->bekal ?? '-';
                                        $harga = 'Rp ' . number_format($item->harga, 0, ',', '.');
                                        $tanggal = $item->tanggal_update ? \Carbon\Carbon::parse($item->tanggal_update)->locale('id')->isoFormat('D MMMM YYYY') : '-';
                                        return [$item->harga_bekal_id => "{$jenisBekal} - {$harga} - {$tanggal}"];
                                    })->toArray();
                                })
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function (callable $set, $state) {
                                    if ($state) {
                                        $hargaBekal = HargaBekal::find($state);
                                        if ($hargaBekal) {
                                            $harga = 'Rp ' . number_format($hargaBekal->harga, 0, ',', '.');
                                            $set('harga_display', $harga);
                                        }
                                    } else {
                                        $set('harga_display', '-');
                                    }
                                })
                                ->helperText('Pilih harga BBM dari 5 data terbaru'),
                            
                            Forms\Components\TextInput::make('harga_display')
                                ->label('Harga BBM')
                                ->disabled()
                                ->dehydrated(false)
                                ->default(function ($record) {
                                    if ($record->harga_bekal_id) {
                                        $hargaBekal = HargaBekal::find($record->harga_bekal_id);
                                        return $hargaBekal ? 'Rp ' . number_format($hargaBekal->harga, 0, ',', '.') : '-';
                                    }
                                    return '-';
                                }),
                        ];
                    })
                    ->action(function (DeliveryOrder $record, array $data) {
                        // Validasi harga_bekal_id
                        $hargaBekalId = $data['harga_bekal_id'] ?? null;
                        
                        if (!$hargaBekalId) {
                            Notification::make()
                                ->title('Error!')
                                ->body('Harga BBM harus dipilih.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Validasi apakah harga_bekal_id valid
                        $hargaBekal = HargaBekal::where('harga_bekal_id', $hargaBekalId)
                            ->where('bekal_id', $record->bekal_id)
                            ->where('kota_id', $record->kota_id)
                            ->first();
                        
                        if (!$hargaBekal) {
                            Notification::make()
                                ->title('Error!')
                                ->body('Harga BBM yang dipilih tidak valid.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Update harga_bekal_id
                        $record->harga_bekal_id = $hargaBekalId;
                        $record->save();
                        
                        Notification::make()
                            ->title('Berhasil')
                            ->body('Harga BBM berhasil diperbarui.')
                            ->success()
                            ->send();
                    })
                    ->visible(function (DeliveryOrder $record) {
                        $user = Auth::user();
                        return $user && $user->level->value === LevelUser::KANPUS->value;
                    }),
                
                // Edit action untuk Kansar (redirect ke halaman edit)
                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->visible(function (DeliveryOrder $record) {
                        $user = Auth::user();
                        
                        // Jika Kanpus atau ABK, hide
                        if ($user && in_array($user->level->value, [LevelUser::KANPUS->value, LevelUser::ABK->value])) {
                            return false;
                        }
                        
                        // Jika Kansar, tombol edit hanya muncul di DO terbaru
                        $latestDo = DeliveryOrder::orderBy('tanggal_do', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        return $latestDo && $latestDo->do_id === $record->do_id;
                    }),
                
                // Delete action untuk Kansar
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Konfirmasi Hapus Data')
                    ->modalDescription('Apakah kamu yakin ingin menghapus data ini? Qty akan dikembalikan ke Sisa SP3M.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->before(function (DeliveryOrder $record) {
                        // Kembalikan qty ke sisa SP3M
                        $sp3m = Sp3m::find($record->sp3m_id);
                        if ($sp3m) {
                            $sp3m->sisa_qty += $record->qty;
                            $sp3m->save();
                            
                            // Kurangi ROB di alpal (pastikan tidak minus)
                            if ($sp3m->alpal_id) {
                                $alpal = \App\Models\Alpal::find($sp3m->alpal_id);
                                if ($alpal) {
                                    $alpal->rob = max(0, $alpal->rob - $record->qty);
                                    $alpal->save();
                                }
                            }
                        }
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Berhasil')
                            ->body('Data delivery order berhasil dihapus dan qty dikembalikan ke Sisa SP3M.')
                    )
                    ->visible(function (DeliveryOrder $record) {
                        $user = Auth::user();
                        
                        // Hanya tampilkan untuk Kansar
                        if (!$user || $user->level->value !== LevelUser::KANSAR->value) {
                            return false;
                        }
                        
                        // Hanya tampilkan di DO terbaru
                        $latestDo = DeliveryOrder::orderBy('tanggal_do', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        return $latestDo && $latestDo->do_id === $record->do_id;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->modalHeading('Konfirmasi Hapus Data')
                        ->modalSubheading('Apakah kamu yakin ingin menghapus data yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalButton('Ya, Hapus Sekarang')
                        ->visible(function () {
                            $user = Auth::user();
                            // Hide untuk ABK
                            return $user && $user->level->value !== LevelUser::ABK->value;
                        }),
                ])
                ->label('Hapus'),
            ])
            ->defaultSort('tanggal_do', 'desc')
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
            'index' => Pages\ListDeliveryOrders::route('/'),
            'create' => Pages\CreateDeliveryOrder::route('/create'),
            'edit' => Pages\EditDeliveryOrder::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        // Semua level bisa melihat menu dan data
        return true;
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        
        // Admin dan Kanpus tidak bisa create
        if ($user && in_array($user->level->value, [LevelUser::ADMIN->value, LevelUser::KANPUS->value])) {
            return false;
        }
        
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
            
        $user = Auth::user();
        
        // Jika user tidak ada, return query kosong
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        
        // Admin dan Kanpus: bisa lihat semua data tanpa filter
        if (in_array($user->level->value, [LevelUser::ADMIN->value, LevelUser::KANPUS->value])) {
            return $query;
        }
        
        // Kansar dan ABK: filter berdasarkan kantor_sar_id
        if ($user->kantor_sar_id) {
            $query->whereHas('sp3m', function ($q) use ($user) {
                $q->where('kantor_sar_id', $user->kantor_sar_id);
            });
        }
        
        return $query;
    }
}
