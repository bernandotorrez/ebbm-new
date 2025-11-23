<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TxBastResource\Pages;
use App\Models\TxBast;
use App\Models\TxSp3k;
use App\Models\DxSp3k;
use App\Models\DxBast;
use App\Models\KantorSar;
use App\Enums\LevelUser;
use App\Traits\RoleBasedResourceAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TxBastResource extends Resource
{
    use RoleBasedResourceAccess;

    protected static ?string $model = TxBast::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'BAST';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'bast';

    public static function getModelLabel(): string
    {
        return 'BAST';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar BAST';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        // Nomor SP3K
                        Forms\Components\Select::make('sp3k_id')
                            ->label('Nomor SP3K')
                            ->required()
                            ->options(static::getSp3kOptions())
                            ->searchable()
                            ->preload()
                            ->validationMessages([
                                'required' => 'Pilih Nomor SP3K',
                            ])
                            ->live()
                            ->afterStateHydrated(function (callable $set, $state, $context) {
                                if ($state && $context === 'edit') {
                                    $sp3k = TxSp3k::with(['kantorSar'])->find($state);
                                    
                                    if ($sp3k) {
                                        $set('tahun_anggaran', $sp3k->tahun_anggaran);
                                        $set('kantor_sar_info', $sp3k->kantorSar->kantor_sar ?? '');
                                        $set('kantor_sar_id', $sp3k->kantor_sar_id);
                                    }
                                }
                            })
                            ->afterStateUpdated(function (callable $get, callable $set, $state, $context) {
                                if ($state) {
                                    $sp3k = TxSp3k::with(['kantorSar', 'details.pelumas.pack', 'details.pelumas.kemasan'])
                                        ->find($state);
                                    
                                    if ($sp3k) {
                                        // Set tahun anggaran
                                        $set('tahun_anggaran', $sp3k->tahun_anggaran);
                                        
                                        // Set kantor sar
                                        $set('kantor_sar_info', $sp3k->kantorSar->kantor_sar ?? '');
                                        $set('kantor_sar_id', $sp3k->kantor_sar_id);
                                        
                                        // Only populate details on create
                                        if ($context === 'create') {
                                            // Get sisa qty dari BAST terakhir
                                            $lastBast = TxBast::where('sp3k_id', $state)
                                                ->orderBy('sequence', 'desc')
                                                ->first();
                                            
                                            $detailsData = [];
                                            
                                            if ($lastBast) {
                                                // Ambil dari BAST terakhir
                                                $lastBastDetails = DxBast::where('bast_id', $lastBast->bast_id)
                                                    ->with('pelumas.pack', 'pelumas.kemasan')
                                                    ->get();
                                                
                                                foreach ($lastBastDetails as $detail) {
                                                    $sisaQty = $detail->sisa_qty_sp3k - $detail->qty_bast;
                                                    
                                                    $detailsData[] = [
                                                        'pelumas_id' => $detail->pelumas_id,
                                                        'pack' => $detail->pelumas->pack->nama_pack ?? '',
                                                        'kemasan_liter' => $detail->pelumas->kemasan->kemasan_liter ?? '',
                                                        'sisa_qty_sp3k' => $sisaQty,
                                                        'qty_bast' => null,
                                                    ];
                                                }
                                            } else {
                                                // Ambil dari SP3K (BAST pertama)
                                                foreach ($sp3k->details as $detail) {
                                                    $detailsData[] = [
                                                        'pelumas_id' => $detail->pelumas_id,
                                                        'pack' => $detail->pelumas->pack->nama_pack ?? '',
                                                        'kemasan_liter' => $detail->pelumas->kemasan->kemasan_liter ?? '',
                                                        'sisa_qty_sp3k' => $detail->qty,
                                                        'qty_bast' => null,
                                                    ];
                                                }
                                            }
                                            
                                            $set('details', $detailsData);
                                        }
                                    }
                                } else {
                                    $set('tahun_anggaran', null);
                                    $set('kantor_sar_info', '');
                                    $set('kantor_sar_id', null);
                                    if ($context === 'create') {
                                        $set('details', []);
                                    }
                                }
                            }),
                        
                        // Tanggal BAST
                        Forms\Components\DatePicker::make('tanggal_bast')
                            ->label('Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection(true),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        // Tahun Anggaran (readonly)
                        Forms\Components\TextInput::make('tahun_anggaran')
                            ->label('Tahun Anggaran (TA)')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        
                        // Kantor SAR (readonly)
                        Forms\Components\TextInput::make('kantor_sar_info')
                            ->label('Kantor SAR')
                            ->disabled()
                            ->dehydrated(false),
                        
                        // Hidden field untuk kantor_sar_id
                        Forms\Components\Hidden::make('kantor_sar_id'),
                    ]),
                
                // Detail BAST (Repeater)
                Forms\Components\Repeater::make('details')
                    ->relationship('details')
                    ->label('Jenis Pelumas')
                    ->schema([
                        Forms\Components\Select::make('pelumas_id')
                            ->label('Jenis Pelumas')
                            ->relationship('pelumas', 'nama_pelumas')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        
                        Forms\Components\TextInput::make('pack')
                            ->label('Pack')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (callable $get, callable $set, $state) {
                                if (!$state) {
                                    $pelumasId = $get('pelumas_id');
                                    if ($pelumasId) {
                                        $pelumas = \App\Models\Pelumas::with('pack')->find($pelumasId);
                                        if ($pelumas && $pelumas->pack) {
                                            $set('pack', $pelumas->pack->nama_pack);
                                        }
                                    }
                                }
                            }),
                        
                        Forms\Components\TextInput::make('kemasan_liter')
                            ->label('Kemasan (Liter)')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (callable $get, callable $set, $state) {
                                if (!$state) {
                                    $pelumasId = $get('pelumas_id');
                                    if ($pelumasId) {
                                        $pelumas = \App\Models\Pelumas::with('kemasan')->find($pelumasId);
                                        if ($pelumas && $pelumas->kemasan) {
                                            $set('kemasan_liter', $pelumas->kemasan->kemasan_liter);
                                        }
                                    }
                                }
                            }),
                        
                        Forms\Components\TextInput::make('sisa_qty_sp3k')
                            ->label('Sisa SP3K')
                            ->disabled()
                            ->dehydrated()
                            ->numeric()
                            ->extraAttributes([
                                'style' => 'font-weight: 600; color: #d97706;'
                            ]),
                        
                        Forms\Components\TextInput::make('qty_bast')
                            ->label('Qty BAST')
                            ->required()
                            ->inputMode('numeric')
                            ->extraInputAttributes([
                                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                            ])
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                            ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                            ->minValue(1)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                $qtyBast = (int) str_replace(['.', ',', ' '], '', $state ?? '0');
                                $sisaQty = $get('sisa_qty_sp3k');
                                
                                if ($qtyBast > $sisaQty) {
                                    $set('qty_error', "Qty BAST melebihi sisa SP3K");
                                } else {
                                    $set('qty_error', null);
                                }
                            })
                            ->helperText(fn ($get) => $get('qty_error') ? 
                                new \Illuminate\Support\HtmlString('<span style="color: #ef4444; font-weight: 600;">' . $get('qty_error') . '</span>') 
                                : null
                            )
                            ->rules([
                                function ($get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $qtyBast = (int) str_replace(['.', ',', ' '], '', $value);
                                        $sisaQty = $get('sisa_qty_sp3k');
                                        
                                        if ($qtyBast > $sisaQty) {
                                            $fail("Qty BAST ({$qtyBast}) melebihi sisa SP3K ({$sisaQty}).");
                                        }
                                    };
                                },
                            ]),
                        
                        Forms\Components\FileUpload::make('file_upload_lampiran')
                            ->label('Lampiran')
                            ->disk('public')
                            ->directory('bast')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable(),
                    ])
                    ->columns(6)
                    ->columnSpan('full')
                    ->collapsible()
                    ->orderColumn('sort')
                    ->reorderable(false)
                    ->addable(false)
                    ->deletable(false)
                    ->defaultItems(0),
            ]);
    }

    protected static function getSp3kOptions(): array
    {
        $user = Auth::user();

        // If user is admin, show all SP3K
        if ($user && $user->level->value === LevelUser::ADMIN->value) {
            return TxSp3k::pluck('nomor_sp3k', 'sp3k_id')->toArray();
        }

        // For non-admin users, only show SP3K from their assigned Kantor SAR
        if ($user && $user->kantor_sar_id) {
            return TxSp3k::where('kantor_sar_id', $user->kantor_sar_id)
                ->pluck('nomor_sp3k', 'sp3k_id')
                ->toArray();
        }

        // If no user or no kantor_sar_id assigned, return empty array
        return [];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sp3k.nomor_sp3k')
                    ->label('Nomor SP3K')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kantorSar.kantor_sar')
                    ->label('Kantor SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_bast')
                    ->label('Tanggal BAST')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sequence')
                    ->label('Sequence')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pelumas_list')
                    ->label('Pelumas')
                    ->html()
                    ->getStateUsing(function ($record) {
                        return $record->details
                            ->map(fn ($detail) => $detail->pelumas?->nama_pelumas)
                            ->filter()
                            ->map(fn ($nama) => "<p>{$nama}</p>")
                            ->implode('');
                    }),
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
                SelectFilter::make('sp3k_id')
                    ->label('SP3K')
                    ->options(static::getSp3kOptions())
                    ->preload(),
                SelectFilter::make('kantor_sar_id')
                    ->label('Kantor SAR')
                    ->relationship('kantorSar', 'kantor_sar')
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
            ]);
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
            'index' => Pages\ListTxBasts::route('/'),
            'create' => Pages\CreateTxBast::route('/create'),
            'edit' => Pages\EditTxBast::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['details.pelumas', 'sp3k', 'kantorSar']);

        $user = Auth::user();
        if ($user && $user->level->value !== LevelUser::ADMIN->value && $user->kantor_sar_id) {
            $query->where('kantor_sar_id', $user->kantor_sar_id);
        }

        return $query;
    }
}
