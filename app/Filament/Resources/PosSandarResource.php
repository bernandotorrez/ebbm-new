<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PosSandarResource\Pages;
use App\Filament\Resources\PosSandarResource\RelationManagers;
use App\Models\PosSandar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosSandarResource extends Resource
{
    protected static ?string $model = PosSandar::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Pos Sandar';

    protected static ?int $navigationSort = 7;

    protected static ?string $slug = 'pos_sandar';

    public static function getModelLabel(): string
    {
        return 'Pos Sandar'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Pos Sandar';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('pos_sandar')
                    ->label('Pos Sandar')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pos_sandar')
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
            'index' => Pages\ListPosSandars::route('/'),
            'create' => Pages\CreatePosSandar::route('/create'),
            'edit' => Pages\EditPosSandar::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
