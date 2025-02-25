<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlpalResource\Pages;
use App\Filament\Resources\AlpalResource\RelationManagers;
use App\Models\Alpal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AlpalResource extends Resource
{
    protected static ?string $model = Alpal::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Alpal';

    protected static ?int $navigationSort = 8;

    protected static ?string $slug = 'alpal';

    public static function getModelLabel(): string
    {
        return 'Alpal'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Alpal';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kantor_sar_id')
                    ->relationship(name: 'kantorSar', titleAttribute: 'kantor_sar')
                    ->label('Kantor SAR')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('tbbm_id')
                    ->relationship(
                        name: 'tbbm',
                        titleAttribute: 'depot',
                        modifyQueryUsing: fn ($query) => $query->with('kota')
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->depot} - {$record->kota?->kota}")
                    ->label('TBBM/DDPU')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('pos_sandar_id')
                    ->relationship(name: 'posSandar', titleAttribute: 'pos_sandar')
                    ->label('Pos Sandar')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('alpal')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('ukuran')
                    ->required()
                    ->label('Ukuran (m)')
                    ->numeric(),
                Forms\Components\TextInput::make('kapasitas')
                    ->required()
                    ->label('Kapasitas (ltr)')
                    ->numeric(),
                Forms\Components\TextInput::make('rob')
                    ->required()
                    ->label('ROB (ltr)')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kantor_sar_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tbbm_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pos_sandar_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('alpal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ukuran')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kapasitas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rob')
                    ->numeric()
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
            'index' => Pages\ListAlpals::route('/'),
            'create' => Pages\CreateAlpal::route('/create'),
            'edit' => Pages\EditAlpal::route('/{record}/edit'),
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
