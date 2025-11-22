<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BekalResource\Pages;
use App\Filament\Resources\BekalResource\RelationManagers;
use App\Models\Bekal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Traits\RoleBasedResourceAccess;

class BekalResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = Bekal::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Jenis Bahan Bakar';

    protected static ?int $navigationSort = 3; // 3. Jenis Bahan Bakar

    protected static ?string $slug = 'jenis-bahan-bakar';

    public static function getModelLabel(): string
    {
        return 'Jenis Bahan Bakar'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Jenis Bahan Bakar';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('golongan_bbm_id')
                    ->relationship(name: 'golonganBbm', titleAttribute: 'golongan')
                    ->label('Jenis Alut')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('satuan_id')
                    ->relationship(name: 'satuan', titleAttribute: 'satuan')
                    ->label('Satuan')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('bekal')
                    ->label('Jenis Bahan Bakar')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('golonganBbm.golongan')
                    ->label('Jenis Alut')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('satuan.satuan')
                    ->label('Satuan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bekal')
                    ->label('Jenis Bahan Bakar')
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
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('golongan_bbm_id')
                ->label('Jenis Alut')
                ->relationship('golonganBbm', 'golongan') // Relasi ke Jenis Alut
                ->preload(), // Untuk memuat data otomatis di dropdown
                SelectFilter::make('satuan_id')
                ->label('Satuan')
                ->relationship('satuan', 'satuan')
                ->preload(),
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
            'index' => Pages\ListBekals::route('/'),
            'create' => Pages\CreateBekal::route('/create'),
            'edit' => Pages\EditBekal::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
