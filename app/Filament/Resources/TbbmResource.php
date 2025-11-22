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
use App\Traits\RoleBasedResourceAccess;

class TbbmResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = Tbbm::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'TBBM/DDPU';

    protected static ?int $navigationSort = 7; // 7. TBBM/DPPU

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
                Forms\Components\TextInput::make('plant')
                    ->label('Plant')
                    ->required()
                    ->maxLength(5),
                Forms\Components\TextInput::make('depot')
                    ->label('Depot')
                    ->required()
                    ->maxLength(50),
                Forms\Components\Select::make('kota_id')
                    ->label('Kota')
                    ->relationship(name: 'kota', titleAttribute: 'kota')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('pbbkb')
                    ->label('PBBKB (%)')
                    ->required()
                    ->maxLength(3)
                    ->rule('numeric')
                    ->rule('min:0')
                    ->rule('max:999')
                    ->suffix('%')
                    ->extraInputAttributes([
                        'onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57',
                        'onkeyup' => 'this.value = this.value.replace(/[^0-9]/g, "")',
                        'type' => 'text'
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plant')
                    ->label('Plant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('depot')
                    ->label('Depot')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kota.kota')
                    ->label('Kota')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pbbkb')
                    ->label('PBBKB (%)')
                    ->numeric()
                    ->sortable()
                    ->suffix('%'),
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
