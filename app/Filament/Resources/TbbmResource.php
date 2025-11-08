<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TbbmResource\Pages;
use App\Filament\Resources\TbbmResource\RelationManagers;
use App\Models\Tbbm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TbbmResource extends Resource
{
    protected static ?string $model = Tbbm::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'TBBM/DDPU';

    protected static ?int $navigationSort = 7;

    protected static ?string $slug = 'tbbm';

    public static function getModelLabel(): string
    {
        return 'TBBM/DDPU'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar TBBM/DDPU';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kota_id')
                    ->relationship(name: 'kota', titleAttribute: 'kota')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('plant')
                    ->required()
                    ->maxLength(5),
                Forms\Components\TextInput::make('depot')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('pbbkb')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                Forms\Components\TextInput::make('ship_to')
                    ->required()
                    ->mask('999999')
                    ->maxLength(6),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kota.kota')
                    ->label('Kota')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('plant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('depot')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pbbkb')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ship_to')
                    ->searchable(),
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
                SelectFilter::make('kota_id')
                ->label('Kota')
                ->relationship('kota', 'kota') // Relasi ke Golongan BBM
                ->preload(), // Untuk memuat data otomatis di dropdown
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
            'index' => Pages\ListTbbms::route('/'),
            'create' => Pages\CreateTbbm::route('/create'),
            'edit' => Pages\EditTbbm::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
