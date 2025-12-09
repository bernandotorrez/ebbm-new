<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GolonganBbmResource\Pages;
use App\Filament\Resources\GolonganBbmResource\RelationManagers;
use App\Models\GolonganBbm;
use App\Traits\RoleBasedResourceAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GolonganBbmResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = GolonganBbm::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Jenis Alut';

    protected static ?int $navigationSort = 1; // 1. Jenis Alut

    protected static ?string $slug = 'jenis-alut';

    public static function getModelLabel(): string
    {
        return 'Jenis Alut'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Jenis Alut';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('golongan')
                    ->label('Jenis Alut')
                    ->placeholder('Contoh: Kapal')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('golongan')
                    ->label('Jenis Alut')
                    ->searchable()
                    ->sortable(),
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
            ->defaultSort('golongan', 'asc')
            ->filters([
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
            'index' => Pages\ListGolonganBbms::route('/'),
            'create' => Pages\CreateGolonganBbm::route('/create'),
            'edit' => Pages\EditGolonganBbm::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
