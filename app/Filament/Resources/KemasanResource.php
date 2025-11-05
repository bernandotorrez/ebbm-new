<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KemasanResource\Pages;
use App\Filament\Resources\KemasanResource\RelationManagers;
use App\Models\Kemasan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KemasanResource extends Resource
{
    protected static ?string $model = Kemasan::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Kemasan';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'kemasan';

    public static function getModelLabel(): string
    {
        return 'Kemasan'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Kemasan'; // Custom title for the table
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kemasan_liter')
                    ->label('Liter')
                    ->required()
                    ->inputMode('numeric')
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")',
                        'maxlength' => '6'
                    ])
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->live(),
                Forms\Components\TextInput::make('kemasan_pack')
                    ->label('Pack')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kemasan_liter')
                    ->label('Liter')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kemasan_pack')
                    ->label('Pack')
                    ->searchable(),
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
            'index' => Pages\ListKemasans::route('/'),
            'create' => Pages\CreateKemasan::route('/create'),
            'edit' => Pages\EditKemasan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}