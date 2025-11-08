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

    protected static ?int $navigationSort = 3;

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
                Forms\Components\Select::make('kantor_sar_id')
                    ->relationship(name: 'kantorSar', titleAttribute: 'kantor_sar')
                    ->label('Kantor SAR')
                    ->options(static::getKantorSarOptions())
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Pilih Kantor SAR',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('nomor_sp3k')
                    ->label('Nomor SP3K')
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
                Forms\Components\DatePicker::make('tanggal_sp3k')
                    ->label('Tanggal SP3K')
                    ->required(),
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
                    ->relationship()
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
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                if ($state) {
                                    $pelumas = \App\Models\Pelumas::find($state);
                                    if ($pelumas) {
                                        $pack = $pelumas->pack;
                                        $kemasan = $pelumas->kemasan;
                                        
                                        $set('pack', $pack ? $pack->nama_pack : '');
                                        $set('kemasan_liter', $kemasan ? $kemasan->kemasan_liter : '');
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
                            ->numeric()
                            ->minValue(1)
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                // Calculate jumlah_isi
                                $qty = (int) $get('qty') ?: 0;
                                $kemasanLiter = (int) $get('kemasan_liter') ?: 0;
                                $set('jumlah_isi', $qty * $kemasanLiter);
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
                    ->itemLabel(fn (array $state): ?string => $state['pelumas_id'] ?? null),
            ]);
    }

    protected static function getKantorSarOptions(): array
    {
        $user = Auth::user();
        
        // If user is admin, show all Kantor SAR
        if ($user && $user->level === LevelUser::ADMIN->value) {
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
                Tables\Columns\TextColumn::make('kantorSar.kantor_sar')
                    ->label('Kantor SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_sp3k')
                    ->label('Nomor SP3K')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_sp3k')
                    ->label('Tanggal SP3K')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tw')
                    ->label('Triwulan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jumlah_qty')
                    ->label('Jumlah Qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_harga')
                    ->label('Jumlah Harga')
                    ->money('IDR')
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
            'index' => Pages\ListSp3ks::route('/'),
            'create' => Pages\CreateSp3k::route('/create'),
            'edit' => Pages\EditSp3k::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
            
        $user = Auth::user();
        
        // Apply user-level filtering for non-admin users
        if ($user && $user->level !== LevelUser::ADMIN->value && $user->kantor_sar_id) {
            $query->where('kantor_sar_id', $user->kantor_sar_id);
        }
        
        return $query;
    }
}