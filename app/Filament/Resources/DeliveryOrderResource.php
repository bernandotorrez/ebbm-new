<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryOrderResource\Pages;
use App\Filament\Resources\DeliveryOrderResource\RelationManagers;
use App\Models\DeliveryOrder;
use App\Models\Sp3m; // Add this import
use App\Models\KantorSar;
use App\Enums\LevelUser;
use App\Traits\RoleBasedResourceAccess;
use Filament\Forms;
use Filament\Forms\Form;
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

    protected static ?int $navigationSort = 3;

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
                // Field yang perlu dipilih user
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
                        // This runs when editing - populate fields from existing SP3M
                        if ($state) {
                            $sp3m = Sp3m::with(['alpal', 'kantorSar', 'bekal'])->find($state);
                            if ($sp3m) {
                                // Set kapal/no reg
                                if ($sp3m->alpal_id && $sp3m->alpal) {
                                    $set('kapal_no_reg', $sp3m->alpal->alpal);
                                } else {
                                    $set('kapal_no_reg', '-');
                                }
                                
                                // Set sisa qty info
                                $set('sisa_qty_info', number_format($sp3m->sisa_qty, 0, ',', '.'));
                                
                                // Set tahun anggaran
                                $set('tahun_anggaran', $sp3m->tahun_anggaran);
                                
                                // Set kantor sar info
                                if ($sp3m->kantorSar) {
                                    $set('kantor_sar_info', $sp3m->kantorSar->kantor_sar);
                                }
                                
                                // Set jenis bahan bakar info
                                if ($sp3m->bekal) {
                                    $set('jenis_bahan_bakar_info', $sp3m->bekal->bekal);
                                }
                                
                                // Set harga satuan
                                $set('harga_satuan', number_format($sp3m->harga_satuan, 0, ',', '.'));
                            }
                        }
                    })
                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                        if ($state) {
                            $sp3m = Sp3m::with(['alpal', 'kantorSar', 'bekal'])->find($state);
                            if ($sp3m) {
                                // Set kapal/no reg
                                if ($sp3m->alpal_id && $sp3m->alpal) {
                                    $set('kapal_no_reg', $sp3m->alpal->alpal);
                                } else {
                                    $set('kapal_no_reg', '-');
                                }

                                // Auto-fill harga_satuan
                                $set('harga_satuan', number_format($sp3m->harga_satuan, 0, ',', '.'));

                                // Set sisa qty info
                                $set('sisa_qty_info', number_format($sp3m->sisa_qty, 0, ',', '.'));
                                
                                // Auto-fill tahun anggaran
                                $set('tahun_anggaran', $sp3m->tahun_anggaran);
                                
                                // Set kantor sar info
                                if ($sp3m->kantorSar) {
                                    $set('kantor_sar_info', $sp3m->kantorSar->kantor_sar);
                                }
                                
                                // Set jenis bahan bakar info
                                if ($sp3m->bekal) {
                                    $set('jenis_bahan_bakar_info', $sp3m->bekal->bekal);
                                }

                                // Recalculate jumlah_harga
                                $qty = (int) str_replace(['.', ',', ' '], '', $get('qty'));
                                $harga = (int) str_replace(['.', ',', ' '], '', $get('harga_satuan'));
                                $set('jumlah_harga', number_format($qty * $harga, 0, ',', '.'));
                            }
                        } else {
                            // Clear fields when no SP3M is selected
                            $set('kapal_no_reg', '');
                            $set('harga_satuan', '');
                            $set('jumlah_harga', '');
                            $set('sisa_qty_info', '');
                            $set('tahun_anggaran', null);
                            $set('kantor_sar_info', '');
                            $set('jenis_bahan_bakar_info', '');
                            $set('pbbkb', null);
                        }
                    }),
                
                // Field otomatis terisi (readonly) - di bagian atas
                Forms\Components\TextInput::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Forms\Components\TextInput::make('kantor_sar_info')
                    ->label('Kantor SAR')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('jenis_bahan_bakar_info')
                    ->label('Jenis Bahan Bakar')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('kapal_no_reg')
                    ->label('Kapal/No Reg')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('sisa_qty_info')
                    ->label('Sisa Qty SP3M')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('harga_satuan')
                    ->label('Harga Satuan')
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : '')
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->required(),
                
                // Field yang perlu diisi user - di bagian bawah
                Forms\Components\Select::make('tbbm_id')
                    ->relationship(name: 'tbbm', titleAttribute: 'depot')
                    ->label('Dari TBBM/DDPU')
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Pilih TBBM/DDPU',
                    ])
                    ->live()
                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                        if ($state) {
                            $tbbm = DB::table('ms_tbbm')->where('tbbm_id', $state)->first();
                            $set('pbbkb', $tbbm->pbbkb);
                        }
                    })
                    ->required(),
                Forms\Components\TextInput::make('nomor_do')
                    ->label('Nomor DO/Nota')
                    ->required()
                    ->maxLength(200),
                Forms\Components\DatePicker::make('tanggal_do')
                    ->label('Tanggal DO')
                    ->required(),
                Forms\Components\TextInput::make('qty')
                    ->required()
                    ->label('Qty')
                    ->inputMode('numeric')
                    ->afterStateUpdated(function (callable $get, callable $set, $state, $livewire) {

                        $qty = (int) str_replace(['.', ',', ' '], '', $get('qty'));
                        $harga = (int) str_replace(['.', ',', ' '], '', $get('harga_satuan'));
                        $pbbkbValue = $get('pbbkb');
                        $pbbkb = is_numeric($pbbkbValue) ? (float) $pbbkbValue / 100 : 0;
                        $ppn = 0.11;

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
                                    $set('qty_error', "Qty melebihi sisa qty SP3M ({$sisaQty})");
                                } else {
                                    $set('qty_error', null);
                                }
                            }
                        }

                        // Jumlah Harga = Harga satuan + (harga satuan * ppn) + (harga satuan * pbbkb) * qty = jumlah harga

                        $jumlah_harga = $qty * ($harga + ($harga * $ppn) + ($harga * $pbbkb));

                        $set('jumlah_harga', number_format($jumlah_harga, 0, ',', '.'));
                    })
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                    ])
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->minValue(0)
                    ->maxLength(10)
                    ->live(debounce: 500)
                    ->helperText(fn ($get) => $get('qty_error') ? 
                        new \Illuminate\Support\HtmlString('<span style="color: #ef4444; font-weight: 600;">' . $get('qty_error') . '</span>') 
                        : null
                    )
                    ->rules([
                        function ($get, $livewire) {
                            return function (string $attribute, $value, \Closure $fail) use ($get, $livewire) {
                                $qty = (int) str_replace(['.', ',', ' '], '', $value);
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
                                            $fail("Qty ({$qty}) melebihi sisa qty SP3M ({$sisaQty}).");
                                        }
                                    }
                                }
                            };
                        },
                    ]),
                Forms\Components\TextInput::make('pbbkb')
                    ->label('PBBKB %')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->minValue(0),
                Forms\Components\TextInput::make('ppn')
                    ->label('PPN %')
                    ->disabled()
                    ->dehydrated()
                    ->default(11)
                    ->numeric()
                    ->minValue(0),
                Forms\Components\TextInput::make('jumlah_harga')
                    ->label('Jumlah Harga')
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : '')
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state)),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\FileUpload::make('file_upload_do')
                        ->required()
                        ->label('File Upload DO')
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
                        ->uploadingMessage('Mengunggah...'),
                        Forms\Components\FileUpload::make('file_upload_laporan')
                            ->required()
                            ->label('File Upload Laporan')
                            ->disk('public')
                            ->directory('delivery-order/laporan')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->validationMessages([
                                'required' => 'File Laporan harus diunggah',
                                'file' => 'File Laporan harus berupa PDF atau gambar',
                                'max' => 'Ukuran file Laporan maksimal 5MB',
                            ])
                            ->uploadingMessage('Mengunggah...')
                ]),
            ]);
    }

    protected static function getSp3mOptions(): array
    {
        $user = Auth::user();
        
        // If user is admin, show all SP3M
        if ($user && $user->level->value === LevelUser::ADMIN->value) {
            return Sp3m::pluck('nomor_sp3m', 'sp3m_id')->toArray();
        }
        
        // For KANSAR and ABK users, only show SP3M from their assigned Kantor SAR
        if ($user && $user->kantor_sar_id && 
            in_array($user->level->value, [LevelUser::KANSAR->value, LevelUser::ABK->value])) {
            return Sp3m::where('kantor_sar_id', $user->kantor_sar_id)
                ->pluck('nomor_sp3m', 'sp3m_id')
                ->toArray();
        }
        
        // For other non-admin users with kantor_sar_id
        if ($user && $user->kantor_sar_id) {
            return Sp3m::where('kantor_sar_id', $user->kantor_sar_id)
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
                $query->with(['sp3m', 'tbbm']);
                
                // Apply user-level filtering for non-admin users
                if ($user && $user->level->value !== LevelUser::ADMIN->value && $user->kantor_sar_id) {
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
                Tables\Columns\TextColumn::make('harga_satuan')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_harga')
                    ->label('Jumlah Harga')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
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
                // Tables\Actions\ViewAction::make()
                //     ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->visible(function (DeliveryOrder $record) {
                        // Cek apakah ini DO terakhir dari SP3M
                        $latestDo = DeliveryOrder::where('sp3m_id', $record->sp3m_id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        return $latestDo && $latestDo->do_id === $record->do_id;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(function (DeliveryOrder $record) {
                        // Cek apakah ini DO terakhir dari SP3M
                        $latestDo = DeliveryOrder::where('sp3m_id', $record->sp3m_id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        return $latestDo && $latestDo->do_id === $record->do_id;
                    })
                    ->before(function (DeliveryOrder $record) {
                        // Kembalikan sisa_qty ke SP3M saat delete
                        $sp3m = Sp3m::find($record->sp3m_id);
                        if ($sp3m) {
                            $sp3m->sisa_qty += $record->qty;
                            $sp3m->save();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->modalHeading('Konfirmasi Hapus Data')
                        ->modalSubheading('Apakah kamu yakin ingin menghapus data yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalButton('Ya, Hapus Sekarang')
                        ->before(function ($records) {
                            // Kembalikan sisa_qty untuk setiap DO yang dihapus
                            foreach ($records as $record) {
                                $sp3m = Sp3m::find($record->sp3m_id);
                                if ($sp3m) {
                                    $sp3m->sisa_qty += $record->qty;
                                    $sp3m->save();
                                }
                            }
                        }),
                ])
                ->label('Hapus'),
            ])
            ->defaultSort('tanggal_do', 'desc');
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
            
        $user = Auth::user();
        
        // Apply user-level filtering for non-admin users through SP3M relationship
        if ($user && $user->level->value !== LevelUser::ADMIN->value && $user->kantor_sar_id) {
            $query->whereHas('sp3m', function ($q) use ($user) {
                $q->where('kantor_sar_id', $user->kantor_sar_id);
            });
        }
        
        return $query;
    }
}
