<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PelumasResource\Pages;
use App\Filament\Resources\PelumasResource\RelationManagers;
use App\Models\Pelumas;
use App\Models\Pack;
use App\Models\Kemasan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use App\Traits\RoleBasedResourceAccess;

class PelumasResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = Pelumas::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Pelumas';

    protected static ?int $navigationSort = 12; // 12. Pelumas

    protected static ?string $slug = 'pelumas';

    public static function getModelLabel(): string
    {
        return 'Pelumas'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Pelumas'; // Custom title for the table
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_pelumas')
                    ->label('Nama Pelumas')
                    ->placeholder('Contoh: Meditrans 15W-40 SAE')
                    ->required()
                    ->maxLength(200),
                Forms\Components\Select::make('pack_id')
                    ->label('Pack')
                    ->relationship('pack', 'nama_pack')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('kemasan_id')
                    ->label('Kemasan')
                    ->relationship('kemasan', 'kemasan_pack')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                        if ($state) {
                            $kemasan = Kemasan::find($state);
                            if ($kemasan) {
                                $set('isi', $kemasan->kemasan_liter);
                            }
                        } else {
                            $set('isi', null);
                        }
                    }),
                Forms\Components\TextInput::make('isi')
                    ->label('Isi')
                    ->required()
                    ->placeholder('Pilih Kemasan')
                    ->inputMode('numeric')
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")',
                        'maxlength' => '6',
                        'class' => 'bg-gray-800 dark:bg-gray-800 text-gray-500 dark:text-gray-400',
                        'style' => 'cursor: not-allowed !important;'
                    ])
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->live()
                    ->readOnly(),
                Forms\Components\TextInput::make('harga')
                    ->label('Harga')
                    ->placeholder('Contoh: 50000')
                    ->required()
                    ->prefix('Rp')
                    ->inputMode('numeric')
                    ->extraInputAttributes([
                        'oninput' => 'if(this.value.length > 20) return; this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")',
                        'maxlength' => '20'
                    ])
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->live(),
                Forms\Components\TextInput::make('tahun')
                    ->label('Tahun')
                    ->placeholder('Contoh: '.date('Y'))
                    ->required()
                    ->inputMode('numeric')
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")',
                        'maxlength' => '4',
                        'minlength' => '4'
                    ])
                    ->live(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_pelumas')
                    ->label('Nama Pelumas')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pack.nama_pack')
                    ->label('Pack')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kemasan.kemasan_pack')
                    ->label('Kemasan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('isi')
                    ->label('Isi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignment('right')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ListPelumases::route('/'),
            'create' => Pages\CreatePelumas::route('/create'),
            'edit' => Pages\EditPelumas::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}