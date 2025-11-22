<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlpalResource\Pages;
use App\Filament\Resources\AlpalResource\RelationManagers;
use App\Models\Alpal;
use App\Models\KantorSar;
use App\Enums\LevelUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use App\Traits\RoleBasedResourceAccess;

class AlpalResource extends Resource
{
    use RoleBasedResourceAccess;
    
    protected static ?string $model = Alpal::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Alut';

    protected static ?int $navigationSort = 9; // 9. Alut

    protected static ?string $slug = 'alut';

    public static function getModelLabel(): string
    {
        return 'Alut'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Alut';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kantor_sar_id')
                    ->relationship(name: 'kantorSar', titleAttribute: 'kantor_sar')
                    ->label('Kantor SAR')
                    ->options(static::getKantorSarOptions())
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
                    ->label('Nama Kapal/No.Reg Pesawat')
                    ->maxLength(100),
                Forms\Components\TextInput::make('ukuran')
                    ->required()
                    ->label('Ukuran (m)')
                    ->maxLength(6)
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                    ])
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->minValue(0),
                Forms\Components\TextInput::make('kapasitas')
                    ->required()
                    ->label('Kapasitas (ltr)')
                    ->maxLength(8)
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                    ])
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->minValue(0),
                Forms\Components\TextInput::make('rob')
                    ->required()
                    ->label('ROB (ltr)')
                    ->maxLength(8)
                    ->extraInputAttributes([
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                    ])
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                    ->minValue(0),
            ]);
    }

    protected static function getKantorSarOptions(): array
    {
        $user = Auth::user();

        // If user is admin, show all Kantor SAR
        if ($user && $user->level->value === LevelUser::ADMIN->value) {
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
                Tables\Columns\TextColumn::make('kantorSar.kantor_sar')
                    ->numeric()
                    ->label('Kantor SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tbbm.depot')
                    ->formatStateUsing(fn ($record) => "{$record->tbbm->depot} - {$record->tbbm->kota?->kota}")
                    ->label('TBBM/DDPU')
                    ->sortable(),
                Tables\Columns\TextColumn::make('posSandar.pos_sandar')
                    ->numeric()
                    ->label('Pos Sandar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('alpal')
                    ->label('Nama Kapal/No.Reg Pesawat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ukuran')
                    ->label('Ukuran (m)')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('kapasitas')
                    ->label('Kapasitas (ltr)')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('rob')
                    ->label('ROB (ltr)')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->label('Dihapus Pada')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Dibuat Pada')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Diperbarui Pada')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kantor_sar_id')
                    ->label('Kantor Sar')
                    ->options(static::getKantorSarOptions())
                    ->preload(),
                SelectFilter::make('tbbm_id')
                    ->label('TBBM/DDPU')
                    ->relationship('tbbm', 'depot', modifyQueryUsing: fn ($query) => $query->with('kota'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->depot} - {$record->kota?->kota}")
                    ->preload(),
                SelectFilter::make('pos_sandar_id')
                    ->label('Pos Sandar')
                    ->relationship('posSandar', 'pos_sandar') // Relasi ke Golongan BBM
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
            'index' => Pages\ListAlpals::route('/'),
            'create' => Pages\CreateAlpal::route('/create'),
            'edit' => Pages\EditAlpal::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        // Apply user-level filtering for non-admin users
        if ($user && $user->level->value !== LevelUser::ADMIN->value && $user->kantor_sar_id) {
            $query->where('kantor_sar_id', $user->kantor_sar_id);
        }

        return $query;
    }
}
