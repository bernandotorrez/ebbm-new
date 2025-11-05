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
use Illuminate\Support\Facades\Auth;
use App\Enums\LevelUser;
use App\Models\KantorSar;

class PemakaianResource extends Resource
{
    protected static ?string $model = Pemakaian::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pemakaian';

    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return 'Pemakaian'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pemakaian';
    }

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
                            ->label('Kantor SAR')
                            ->options(static::getKantorSarOptions()),

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
                            ->maxValue(99999)  // Adjusted for 5 digits as per schema
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

    protected static function getKantorSarOptions(): array
    {
        $user = Auth::user();
        
        // If user is admin, show all Kantor SAR
        if ($user && $user->level === LevelUser::ADMIN->value) {
            return KantorSar::pluck('kantor_sar', 'kantor_sar_id')->toArray();
        }
        
        // For non-admin users, only show their assigned Kantor SAR
        if ($user && $user->kantor_sar_id) {
            return KantorSar::where('kantor_sar_id', $user->kantor_sar_id)
                ->pluck('kantor_sar', 'kantor_sar_id')
                ->toArray();
        }
        
        // If no user or no kantor_sar_id assigned, return empty array
        return [];
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
            'index' => Pages\ListPemakaians::route('/'),
            'create' => Pages\CreatePemakaian::route('/create'),
            'edit' => Pages\EditPemakaian::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
            
        $user = Auth::user();
        
        // Apply user-level filtering for non-admin users
        if ($user && $user->level !== LevelUser::ADMIN->value && $user->kantor_sar_id) {
            $query->where('kantor_sar_id', $user->kantor_sar_id);
        }
        
        return $query;
    }
}