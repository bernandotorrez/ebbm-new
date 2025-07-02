<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Sp3mResource\Pages;
use App\Filament\Resources\Sp3mResource\RelationManagers;
use App\Models\Sp3m;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class Sp3mResource extends Resource
{
    protected static ?string $model = Sp3m::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'SP3M';

    protected static ?int $navigationSort = 9;

    protected static ?string $slug = 'sp3m';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nomor_sp3m')
                    ->required()
                    ->maxLength(200),
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
                Forms\Components\TextInput::make('tw')
                    ->required()
                    ->maxLength(25),
                Forms\Components\Select::make('kantor_sar_id')
                    ->relationship(name: 'kantorSar', titleAttribute: 'kantor_sar')
                    ->label('Kantor SAR')
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Pilih Kantor SAR',
                    ])
                    ->required(),
                Forms\Components\Select::make('alpal_id')
                    ->relationship(name: 'alpal', titleAttribute: 'alpal')
                    ->label('Alpal')
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Pilih Alpal',
                    ])
                    ->required(),
                Forms\Components\Select::make('bekal_id')
                    ->relationship(name: 'bekal', titleAttribute: 'bekal')
                    ->label('Bekal')
                    ->searchable()
                    ->preload()
                    ->validationMessages([
                        'required' => 'Pilih Bekal',
                    ])
                    ->required(),
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
                    ->maxLength(5)
                    ->live(),
                Forms\Components\TextInput::make('harga_satuan')
                    ->required()
                    ->label('Harga Satuan')
                    ->prefix('Rp')
                    ->inputMode('numeric')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_sp3m')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tahun_anggaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tw')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kantorSar.kantor_sar')
                    ->numeric()
                    ->label('Kantor Sar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('alpal.alpal')
                    ->numeric()
                    ->label('Alpal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bekal.bekal')
                    ->numeric()
                    ->label('Bekal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_satuan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_harga')
                    ->numeric()
                    ->sortable(),
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
                SelectFilter::make('kantor_sar_id')
                    ->label('Kantor SAR')
                    ->relationship('kantorSar', 'kantor_sar') // Relasi ke Golongan BBM
                    ->preload(),
                SelectFilter::make('alpal_id')
                    ->label('Alpal')
                    ->relationship('alpal', 'alpal') // Relasi ke Golongan BBM
                    ->preload(),
                SelectFilter::make('bekal_id')
                    ->label('Bekal')
                    ->relationship('bekal', 'bekal') // Relasi ke Golongan BBM
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
