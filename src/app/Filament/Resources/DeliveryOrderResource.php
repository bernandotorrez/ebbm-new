<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryOrderResource\Pages;
use App\Filament\Resources\DeliveryOrderResource\RelationManagers;
use App\Models\DeliveryOrder;
use App\Models\Sp3m; // Add this import
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryOrderResource extends Resource
{
    protected static ?string $model = DeliveryOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Delivery Order';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'delivery-order';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('sp3m_id')
                    ->relationship(name: 'sp3m', titleAttribute: 'nomor_sp3m')
                    ->label('Nomor SP3M')
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Pilih Nomor SP3M',
                    ])
                    ->required()
                    ->live()
                    ->afterStateHydrated(function (callable $set, $state) {
                        // This runs when editing - populate kapal_no_reg from existing SP3M
                        if ($state) {
                            $sp3m = Sp3m::find($state);
                            if ($sp3m) {
                                if ($sp3m->alpal_id) {
                                    $alpal = DB::table('alpals')->where('alpal_id', $sp3m->alpal_id)->first();
                                    $set('kapal_no_reg', $alpal->alpal);
                                } else {
                                    $set('kapal_no_reg', '-');
                                }
                            }
                        }
                    })
                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                        if ($state) {
                            $sp3m = Sp3m::find($state);
                            if ($sp3m) {
                                if ($sp3m->alpal_id) {
                                    $alpal = DB::table('alpals')->where('alpal_id', $sp3m->alpal_id)->first();

                                    $set('kapal_no_reg', $alpal->alpal);
                                } else {
                                    $set('kapal_no_reg', '-');
                                }

                                // Auto-fill harga_satuan
                                $set('harga_satuan', number_format($sp3m->harga_satuan, 0, ',', '.'));

                                // Optionally auto-fill qty if needed
                                // $set('qty', number_format($sp3m->qty, 0, ',', '.'));

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
                        }
                    }),
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
                            $tbbm = DB::table('tbbms')->where('tbbm_id', $state)->first();
                            $set('pbbkb', $tbbm->pbbkb);
                        }
                    })
                    ->required(),
                Forms\Components\DatePicker::make('tanggal_do')
                    ->required(),
                Forms\Components\Select::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->required()
                    ->options(function () {
                        return DB::table('pagus')
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
                Forms\Components\TextInput::make('nomor_do')
                    ->label('Nomor DO/Nota')
                    ->required()
                    ->maxLength(200),
                Forms\Components\TextInput::make('kapal_no_reg')
                    ->label('Kapal/No Reg')
                    ->readOnly()
                    ->extraAttributes([
                        'class' => '!bg-black-100 !text-gray-500 !cursor-not-allowed !border-gray-200'
                    ])
                    ->required(),
                Forms\Components\TextInput::make('qty')
                    ->required()
                    ->label('Qty')
                    ->inputMode('numeric')
                    ->afterStateUpdated(function (callable $get, callable $set) {

                        $qty = (int) $get('qty');
                        $harga = (int) str_replace(['.', ',', ' '], '', $get('harga_satuan'));
                        $pbbkb = (int) number_format($get('pbbkb'), 0, ',', '.') / 100;
                        $ppn = 0.11;

                        // Jumlah Harga = Harga satuan + (harga satuan * ppn) + (harga satuan * pbbkb) * qty = jumlah harga

                        $jumlah_harga = $qty * ($harga + ($harga * $ppn) + ($harga * $pbbkb));

                        $set('jumlah_harga', number_format($jumlah_harga, 0, ',', '.'));
                    })
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                    ])
                    ->maxLength(5)
                    ->live(),
                Forms\Components\TextInput::make('harga_satuan')
                    ->required()
                    ->label('Harga Satuan')
                    ->prefix('Rp')
                    ->inputMode('numeric')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : '')
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace('.', '', $state))
                    ->extraInputAttributes([
                        'class' => 'bg-black-100 cursor-not-allowed opacity-60'
                    ])
                    ->readonly(),
                Forms\Components\TextInput::make('pbbkb')
                    ->label('PKBB %')
                    ->readOnly()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : '')
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace('.', '', $state))
                    ->numeric(),
                Forms\Components\TextInput::make('ppn')
                    ->required()
                    ->readOnly()
                    ->label('PPN %')
                    ->default(11)
                    ->numeric(),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('jumlah_harga')
                            ->required()
                            ->label('Jumlah Harga')
                            ->prefix('Rp')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : '')
                            ->dehydrateStateUsing(fn ($state) => (int) str_replace('.', '', $state))
                            ->readonly(),
                        Forms\Components\Placeholder::make('empty_field')
                            ->label('')
                            ->content(''),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\FileUpload::make('file_upload_do')
                        ->required()
                        ->label('File Upload DO')
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

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->with(['sp3m', 'tbbm'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('sp3m.nomor_sp3m')
                    ->label('Nomor SP3M')
                    ->searchable()
                    ->sortable(),
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
                    ->label('Qty')
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('sp3m_id')
                    ->label('SP3M')
                    ->relationship('sp3m', 'nomor_sp3m')
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
                        return DB::table('pagus')
                            ->select('tahun_anggaran')
                            ->distinct()
                            ->orderBy('tahun_anggaran', 'desc')
                            ->pluck('tahun_anggaran', 'tahun_anggaran')
                            ->toArray();
                    }),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
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
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
