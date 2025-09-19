<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PemakaianResource\Pages;
use App\Models\Pemakaian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PemakaianResource extends Resource
{
    protected static ?string $model = Pemakaian::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pemakaian';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Pemakaian')
                    ->description('Masukkan informasi pemakaian barang')
                    ->schema([
                        Forms\Components\Select::make('kantor_sar_id')
                            ->relationship('kantorSar', 'kantor_sar')  // Changed from 'nama' to 'kantor_sar'
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Kantor SAR'),

                        Forms\Components\Select::make('alpal_id')
                            ->relationship('alpal', 'alpal')  // Changed from 'nama' to 'alpal'
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Alat & Perlengkapan'),

                        Forms\Components\Select::make('bekal_id')
                            ->relationship('bekal', 'bekal')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Bekal'),

                        Forms\Components\DatePicker::make('tanggal_pakai')
                            ->required()
                            ->maxDate(now())
                            ->label('Tanggal Pakai'),

                        Forms\Components\TextInput::make('qty')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->label('Quantity'),

                        Forms\Components\Textarea::make('keterangan')
                            ->required()
                            ->maxLength(1000)
                            ->rows(3)
                            ->label('Keterangan'),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kantorSar.kantor_sar')  // Changed from 'nama' to 'kantor_sar'
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('alpal.alpal')  // Changed from 'nama' to 'alpal'
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('bekal.bekal')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_pakai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kantor_sar_id')
                    ->relationship('kantorSar', 'kantor_sar')  // Changed from 'nama' to 'kantor_sar'
                    ->searchable()
                    ->preload()
                    ->label('Kantor SAR'),
                Tables\Filters\SelectFilter::make('alpal_id')
                    ->relationship('alpal', 'alpal')  // Changed from 'nama' to 'alpal'
                    ->searchable()
                    ->preload()
                    ->label('Alat & Perlengkapan'),
                Tables\Filters\SelectFilter::make('bekal_id')
                    ->relationship('bekal', 'bekal')
                    ->searchable()
                    ->preload()
                    ->label('Bekal'),
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
            'index' => Pages\ListPemakaians::route('/'),
            'create' => Pages\CreatePemakaian::route('/create'),
            'edit' => Pages\EditPemakaian::route('/{record}/edit'),
        ];
    }
}
