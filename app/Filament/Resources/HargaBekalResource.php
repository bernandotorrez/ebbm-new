<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HargaBekalResource\Pages;
use App\Filament\Resources\HargaBekalResource\RelationManagers;
use App\Models\HargaBekal;
use App\Traits\RoleBasedResourceAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HargaBekalResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = HargaBekal::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Master';
    protected static ?string $navigationLabel = 'Harga BBM';
    protected static ?int $navigationSort = 8; // Place at the end of Master menu
    
    public static function getModelLabel(): string
    {
        return 'Harga BBM'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Harga BBM'; // Plural name
    }
    
    public static function canAccess(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kota_id')
                    ->label('Kota')
                    ->relationship('kota', 'kota')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Select::make('bekal_id')
                    ->label('Bekal')
                    ->relationship('bekal', 'bekal')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\TextInput::make('harga')
                    ->required()
                    ->label('Harga')
                    ->prefix('Rp')
                    ->inputMode('numeric')
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                    ])
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->live(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kota.kota')
                    ->label('Kota')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('bekal.bekal')
                    ->label('Bekal')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignment('right')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Dihapus Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
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
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen')
                        ->modalHeading('Konfirmasi Hapus Permanen Data')
                        ->modalSubheading('Apakah kamu yakin ingin menghapus data yang dipilih secara permanen? Tindakan ini tidak dapat dibatalkan.')
                        ->modalButton('Ya, Hapus Permanen'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan')
                        ->modalHeading('Konfirmasi Pulihkan Data')
                        ->modalSubheading('Apakah kamu yakin ingin memulihkan data yang dipilih?')
                        ->modalButton('Ya, Pulihkan Sekarang'),
                ])
                ->label('Hapus'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
            'index' => Pages\ListHargaBekals::route('/'),
            'create' => Pages\CreateHargaBekal::route('/create'),
            'edit' => Pages\EditHargaBekal::route('/{record}/edit'),
        ];
    }
}
