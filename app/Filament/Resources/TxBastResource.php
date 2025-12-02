<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TxBastResource\Pages;
use App\Models\TxBast;
use App\Models\TxSp3k;
use App\Models\DxSp3k;
use App\Models\DxBast;
use App\Models\Pelumas;
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
                            ->afterStateUpdated(function (callable $get, callable $set, $state, $context) {
                                if ($state && $context === 'create') {
                                    $sp3k = TxSp3k::with(['details.pelumas'])->find($state);
                                    
                                    if ($sp3k) {
                                        // Get BAST terakhir untuk SP3K ini
                                        $lastBast = TxBast::where('sp3k_id', $state)
                                            ->orderBy('bast_ke', 'desc')
                                            ->first();
                                        
                                        $detailsData = [];
                                        
                                        if ($lastBast) {
                                            // Ambil dari BAST terakhir
                                            $lastBastDetails = DxBast::where('bast_id', $lastBast->bast_id)
                                                ->with('pelumas')
                                                ->get();
                                            
                                            foreach ($lastBastDetails as $detail) {
                                                // Hitung qty_diterima baru = qty_diterima lama + qty_masuk lama
                                                $qtyDiterimaNew = $detail->qty_diterima + $detail->qty_masuk;
                                                // Hitung qty_terutang baru = qty_mulai - qty_diterima baru
                                                $qtyTerutangNew = $detail->qty_mulai - $qtyDiterimaNew;
                                                
                                                // Hitung harga - gunakan nilai dari BAST terakhir + harga masuk terakhir
                                                $jumlahHargaDiterimaNew = $detail->jumlah_harga_diterima + $detail->jumlah_harga_masuk;
                                                $jumlahHargaTerutangNew = $detail->jumlah_harga_mulai - $jumlahHargaDiterimaNew;
                                                
                                                $detailsData[] = [
                                                    'pelumas_id' => $detail->pelumas_id,
                                                    'qty_mulai' => $detail->qty_mulai,
                                                    'qty_diterima' => $qtyDiterimaNew,
                                                    'qty_terutang' => $qtyTerutangNew,
                                                    'qty_masuk' => null,
                                                    'jumlah_harga_mulai' => $detail->jumlah_harga_mulai,
                                                    'jumlah_harga_diterima' => $jumlahHargaDiterimaNew,
                                                    'jumlah_harga_terutang' => $jumlahHargaTerutangNew,
                                                ];
                                            }
                                        } else {
                                            // BAST pertama - ambil dari SP3K
                                            foreach ($sp3k->details as $detail) {
                                                $pelumas = $detail->pelumas;
                                                $hargaSatuan = $detail->harga;
                                                $totalHarga = $detail->qty * $hargaSatuan;
                                                
                                                $detailsData[] = [
                                                    'pelumas_id' => $detail->pelumas_id,
                                                    'qty_mulai' => $detail->qty,
                                                    'qty_diterima' => 0,
                                                    'qty_terutang' => $detail->qty,
                                                    'qty_masuk' => null,
                                                    'jumlah_harga_mulai' => $totalHarga,
                                                    'jumlah_harga_diterima' => 0,
                                                    'jumlah_harga_terutang' => $totalHarga,
                                                ];
                                            }
                                        }
                                        
                                        $set('details', $detailsData);
                                    }
                                } else if (!$state && $context === 'create') {
                                    $set('details', []);
                                }
                            }),
                        
                        // Tanggal BAST
                        Forms\Components\DatePicker::make('tanggal_bast')
                            ->label('Tanggal BAST')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection(true),
                    ]),
                
                // Detail BAST (Repeater)
                Forms\Components\Repeater::make('details')
                    ->relationship('details')
                    ->label('Detail Pelumas')
                    ->schema([
                        Forms\Components\Select::make('pelumas_id')
                            ->label('Pelumas')
                            ->relationship('pelumas', 'nama_pelumas')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        
                        Forms\Components\TextInput::make('qty_mulai')
                            ->label('Qty Mulai')
                            ->disabled()
                            ->dehydrated()
                            ->numeric(),
                        
                        Forms\Components\TextInput::make('qty_diterima')
                            ->label('Qty Diterima')
                            ->disabled()
                            ->dehydrated()
                            ->numeric()
                            ->extraAttributes(['style' => 'font-weight: 600; color: #059669;']),
                        
                        Forms\Components\TextInput::make('qty_terutang')
                            ->label('Qty Terutang')
                            ->disabled()
                            ->dehydrated()
                            ->numeric()
                            ->extraAttributes(['style' => 'font-weight: 600; color: #d97706;'])
                            ->afterStateHydrated(function (callable $get, callable $set, $state) {
                                // Jika qty_terutang = 0, set jumlah_harga_masuk = 0
                                if ((int) $state === 0) {
                                    $set('jumlah_harga_masuk', 0);
                                }
                            }),
                        
                        Forms\Components\TextInput::make('qty_masuk')
                            ->label('Qty Masuk')
                            ->required(fn (callable $get) => (int) $get('qty_terutang') > 0)
                            ->disabled(fn (callable $get) => (int) $get('qty_terutang') === 0)
                            ->default(fn (callable $get) => (int) $get('qty_terutang') === 0 ? '0' : null)
                            ->inputMode('numeric')
                            ->extraInputAttributes([
                                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")',
                                'maxlength' => '5'
                            ])
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                $qtyMasuk = (int) $state;
                                $qtyTerutang = (int) $get('qty_terutang');
                                
                                if ($qtyMasuk > $qtyTerutang) {
                                    $set('qty_error', "Qty Masuk melebihi Qty Terutang");
                                } else {
                                    $set('qty_error', null);
                                    
                                    // Calculate harga
                                    $pelumasId = $get('pelumas_id');
                                    if ($pelumasId) {
                                        $pelumas = Pelumas::find($pelumasId);
                                        if ($pelumas) {
                                            $hargaSatuan = $pelumas->harga ?? 0;
                                            $jumlahHargaMasuk = $qtyMasuk * $hargaSatuan;
                                            $set('jumlah_harga_masuk', $jumlahHargaMasuk);
                                        }
                                    }
                                }
                            })
                            ->helperText(fn ($get) => $get('qty_error') ? 
                                new \Illuminate\Support\HtmlString('<span style="color: #ef4444; font-weight: 600;">' . $get('qty_error') . '</span>') 
                                : null
                            )
                            ->rules([
                                'numeric',
                                'min:0',
                                function ($get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $qtyMasuk = (int) $value;
                                        $qtyTerutang = (int) $get('qty_terutang');
                                        
                                        if ($qtyMasuk > $qtyTerutang) {
                                            $fail("Qty Masuk ({$qtyMasuk}) melebihi Qty Terutang ({$qtyTerutang}).");
                                        }
                                    };
                                },
                            ]),
                        
                        Forms\Components\Hidden::make('jumlah_harga_mulai')
                            ->default(0)
                            ->dehydrated(),
                        Forms\Components\Hidden::make('jumlah_harga_diterima')
                            ->default(0)
                            ->dehydrated(),
                        Forms\Components\Hidden::make('jumlah_harga_terutang')
                            ->default(0)
                            ->dehydrated(),
                        Forms\Components\Hidden::make('jumlah_harga_masuk')
                            ->default(0)
                            ->dehydrated()
                            ->afterStateHydrated(function (callable $get, callable $set, $state) {
                                // Jika qty_terutang = 0, set jumlah_harga_masuk = 0
                                $qtyTerutang = (int) $get('qty_terutang');
                                if ($qtyTerutang === 0) {
                                    $set('jumlah_harga_masuk', 0);
                                }
                            }),
                    ])
                    ->columns(5)
                    ->columnSpan('full')
                    ->collapsible()
                    ->reorderable(false)
                    ->addable(false)
                    ->deletable(false)
                    ->defaultItems(0),
            ]);
    }

    protected static function getSp3kOptions(): array
    {
        $user = Auth::user();

        // Hanya tampilkan SP3K yang belum selesai (bast_sudah_diterima_semua = 0)
        if ($user && $user->level->value === LevelUser::ADMIN->value) {
            return TxSp3k::where('bast_sudah_diterima_semua', '0')
                ->pluck('nomor_sp3k', 'sp3k_id')
                ->toArray();
        }

        if ($user && $user->kantor_sar_id) {
            return TxSp3k::where('kantor_sar_id', $user->kantor_sar_id)
                ->where('bast_sudah_diterima_semua', '0')
                ->pluck('nomor_sp3k', 'sp3k_id')
                ->toArray();
        }

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
                Tables\Columns\TextColumn::make('sp3k.kantorSar.kantor_sar')
                    ->label('Kantor SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_bast')
                    ->label('Tanggal BAST')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bast_ke')
                    ->label('BAST Ke')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('sp3k.bast_sudah_diterima_semua')
                    ->label('Status SP3K')
                    ->formatStateUsing(fn ($state) => $state === '1' ? 'Selesai' : 'Outstanding')
                    ->colors([
                        'success' => '1',
                        'warning' => '0',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('sp3k_id')
                    ->label('SP3K')
                    ->options(static::getSp3kOptions())
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->visible(function ($record) {
                        $user = Auth::user();
                        // Hide untuk Kanpus
                        if ($user && $user->level->value === LevelUser::KANPUS->value) {
                            return false;
                        }
                        // Hide jika SP3K sudah selesai
                        return $record->sp3k && $record->sp3k->bast_sudah_diterima_semua === '0';
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->visible(function () {
                            $user = Auth::user();
                            // Hide untuk Kanpus
                            return $user && $user->level->value !== LevelUser::KANPUS->value;
                        }),
                ])
                ->label('Hapus')
                ->visible(function () {
                    $user = Auth::user();
                    // Hide untuk Kanpus
                    return $user && $user->level->value !== LevelUser::KANPUS->value;
                }),
            ])
            ->recordUrl(null);
    }

    public static function getRelations(): array
    {
        return [];
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
            ->with(['details.pelumas', 'sp3k.kantorSar']);

        $user = Auth::user();
        if ($user && $user->level->value !== LevelUser::ADMIN->value && $user->kantor_sar_id) {
            $query->whereHas('sp3k', function ($q) use ($user) {
                $q->where('kantor_sar_id', $user->kantor_sar_id);
            });
        }

        return $query;
    }
}
