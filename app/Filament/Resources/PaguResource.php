<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaguResource\Pages;
use App\Filament\Resources\PaguResource\RelationManagers;
use App\Models\Pagu;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;

class PaguResource extends Resource
{
    protected static ?string $model = Pagu::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pagu';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'pagu';

    public static function getModelLabel(): string
    {
        return 'Pagu'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Pagu';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('golongan_bbm_id')
                    ->relationship(name: 'golonganBbm', titleAttribute: 'golongan')
                    ->label('Golongan')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('nilai_pagu')
                    ->required()
                    ->label('Nilai Pagu')
                    ->prefix('Rp')
                    ->inputMode('numeric')
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                    ])
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->live(),
                Forms\Components\TextInput::make('tahun_anggaran')
                    ->required()
                    ->numeric()
                    ->maxLength(4)
                    ->minLength(4)
                    ->minValue(1900)
                    ->maxValue(2099)
                    ->placeholder('YYYY')
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").slice(0, 4)'
                    ])
                    ->rules(['digits:4', 'min:1900', 'max:2099']),
                Forms\Components\TextInput::make('dasar')
                    ->required()
                    ->maxLength(50),
                Forms\Components\DatePicker::make('tanggal')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('golonganBbm.golongan')
                    ->numeric()
                    ->label('Golongan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nilai_pagu')
                    ->numeric()
                    ->label('Nilai Pagu')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignment('right')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dasar')
                    ->label('Dasar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
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
                SelectFilter::make('golongan_bbm_id')
                    ->label('Golongan')
                    ->relationship('golonganBbm', 'golongan') // Relasi ke Golongan BBM
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
            'index' => Pages\ListPagus::route('/'),
            'create' => Pages\CreatePagu::route('/create'),
            'edit' => Pages\EditPagu::route('/{record}/edit'),
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
