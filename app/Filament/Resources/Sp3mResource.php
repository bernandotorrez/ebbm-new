<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Sp3mResource\Pages;
use App\Filament\Resources\Sp3mResource\RelationManagers;
use App\Models\Sp3m;
use App\Models\Alpal;
use App\Models\KantorSar;
use App\Models\HargaBekal;
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

class Sp3mResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = Sp3m::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'SP3M';

    protected static ?int $navigationSort = 2;

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
                Forms\Components\Select::make('alpal_id')
                    ->relationship(name: 'alpal', titleAttribute: 'alpal')
                    ->label('Alut')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function (callable $get, callable $set) {
                        $alpalId = $get('alpal_id');

                        if ($alpalId) {
                            $alpal = Alpal::find($alpalId);
                            $set('kantor_sar_id', $alpal ? $alpal->kantor_sar_id : null);
                        } else {
                            $set('kantor_sar_id', null);
                        }
                    })
                    ->validationMessages([
                        'required' => 'Pilih Alut',
                    ])
                    ->required(),
                Forms\Components\Select::make('kantor_sar_id')
                    ->relationship(name: 'kantorSar', titleAttribute: 'kantor_sar')
                    ->label('Kantor SAR')
                    ->options(static::getKantorSarOptions())
                    ->disabled()
                    ->dehydrated() // Tetap kirim nilai meskipun disabled
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Pilih Kantor SAR',
                    ])
                    ->required(),
                Forms\Components\Select::make('bekal_id')
                    ->relationship(name: 'bekal', titleAttribute: 'bekal')
                    ->label('Bekal')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function (callable $get, callable $set) {
                        $bekalId = $get('bekal_id');

                        if ($bekalId) {
                            // Try get the latest harga for this bekal
                            $harga = HargaBekal::where('bekal_id', $bekalId)
                                ->orderBy('created_at', 'desc')
                                ->value('harga');

                            // set formatted string so the readonly input shows thousand separators immediately
                            $set('harga_satuan', $harga !== null ? number_format((int) $harga, 0, ',', '.') : null);
                        } else {
                            $set('harga_satuan', 0);
                        }
                    })
                    ->validationMessages([
                        'required' => 'Pilih Bekal',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('nomor_sp3m')
                    ->label('Nomor SP3M')
                    ->required()
                    ->maxLength(200),
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
                    ->preload(),
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
                Forms\Components\TextInput::make('qty')
                    ->required()
                    ->label('Qty')
                    ->inputMode('numeric')
                    ->afterStateUpdated(function (callable $get, callable $set) {
                        $qty = (int) str_replace(['.', ',', ' '], '', $get('qty'));
                        $harga = (int) str_replace(['.', ',', ' '], '', $get('harga_satuan'));
                        $set('jumlah_harga', number_format($qty * $harga, 0, ',', '.'));
                    })
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                    ])
                    ->minValue(0)
                    ->maxLength(10)
                    ->live(),
                Forms\Components\TextInput::make('harga_satuan')
                    ->required()
                    ->label('Harga Satuan')
                    ->prefix('Rp')
                    ->inputMode('numeric')
                    ->readonly()
                    ->afterStateUpdated(function (callable $get, callable $set) {
                        $qty = (int) str_replace(['.', ',', ' '], '', $get('qty'));
                        $harga = (int) str_replace(['.', ',', ' '], '', $get('harga_satuan'));
                        $set('jumlah_harga', number_format($qty * $harga, 0, ',', '.'));
                    })
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                    ])
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->live(),
                Forms\Components\TextInput::make('jumlah_harga')
                    ->required()
                    ->label('Jumlah Harga')
                    ->prefix('Rp')
                    ->readonly()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->extraInputAttributes([
                        'inputmode' => 'numeric',
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\FileUpload::make('file_upload_sp3m')
                            ->required()
                            ->label('File Upload SP3M')
                            ->disk('public')
                            ->directory('sp3m')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->validationMessages([
                                'required' => 'File SP3M harus diunggah',
                                'file' => 'File SP3M harus berupa PDF atau gambar',
                                'max' => 'Ukuran file SP3M maksimal 5MB',
                            ])
                            ->uploadingMessage('Mengunggah...'),

                        Forms\Components\FileUpload::make('file_upload_kelengkapan_sp3m')
                            ->required()
                            ->label('File Upload Kelengkapan SP3M')
                            ->disk('public')
                            ->directory('sp3m/kelengkapan')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->validationMessages([
                                'required' => 'File SP3M harus diunggah',
                                'file' => 'File SP3M harus berupa PDF atau gambar',
                                'max' => 'Ukuran file SP3M maksimal 5MB',
                            ])
                            ->uploadingMessage('Mengunggah...'),
                    ]),
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
                    ->label('Bekal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_sp3m')
                    ->label('Nomor SP3M')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tw')
                    ->label('Triwulan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Kuantitas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_satuan')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_harga')
                    ->label('Jumlah Harga')
                    ->numeric()
                    ->sortable(),
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
                SelectFilter::make('kantor_sar_id')
                    ->label('Kantor SAR')
                    ->options(static::getKantorSarOptions())
                    ->preload(),
                SelectFilter::make('alpal_id')
                    ->label('Alpal')
                    ->relationship('alpal', 'alpal') // Relasi ke Golongan BBM
                    ->preload(),
                SelectFilter::make('bekal_id')
                    ->label('Bekal')
                    ->relationship('bekal', 'bekal') // Relasi ke Golongan BBM
                    ->preload(),
                SelectFilter::make('tahun_anggaran')
                    ->label('Tahun Anggaran'),
                // Tables\Filters\TrashedFilter::make(),
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
            'index' => Pages\ListSp3ms::route('/'),
            'create' => Pages\CreateSp3m::route('/create'),
            'edit' => Pages\EditSp3m::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        // Apply user-level filtering for non-admin users
        if ($user && $user->level->value !== LevelUser::ADMIN->value && $user->kantor_sar_id) {
            $query->where('kantor_sar_id', $user->kantor_sar_id);
        }

        return $query;
    }
}
