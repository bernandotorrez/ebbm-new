<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KantorSarResource\Pages;
use App\Filament\Resources\KantorSarResource\RelationManagers;
use App\Models\KantorSar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Traits\RoleBasedResourceAccess;

class KantorSarResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = KantorSar::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Kantor SAR';

    protected static ?int $navigationSort = 6; // 6. Kantor SAR

    protected static ?string $slug = 'kantor-sar';

    public static function getModelLabel(): string
    {
        return 'Kantor SAR'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Kantor Sar';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kantor_sar')
                    ->label('Kantor SAR')
                    ->placeholder('Contoh: Kantor SAR Jakarta')
                    ->required()
                    ->maxLength(50),
                
                Forms\Components\Select::make('kota_id')
                    ->label('Kota')
                    ->relationship('kota', 'kota')
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kantor_sar')
                    ->label('Kantor SAR')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('kota.kota')
                    ->label('Kota')
                    ->sortable()
                    ->searchable(),
                
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
            'index' => Pages\ListKantorSars::route('/'),
            'create' => Pages\CreateKantorSar::route('/create'),
            'edit' => Pages\EditKantorSar::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
