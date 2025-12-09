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
                    ->placeholder('Contoh: 1234')
                    ->required()
                    ->maxLength(4)
                    ->extraInputAttributes([
                        'type' => 'text',
                        'maxlength' => '4',
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "")',
                    ])
                    ->rules(['required', 'regex:/^[0-9]{1,4}$/']),
                Forms\Components\TextInput::make('depot')
                    ->label('Depot')
                    ->placeholder('Contoh: Pelumpang')
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
                    ->placeholder('Contoh: 10')
                    ->required()
                    ->maxLength(6)
                    ->suffix('%')
                    ->extraInputAttributes([
                        'type' => 'text',
                        'maxlength' => '6',
                        'oninput' => 'this.value = this.value.replace(/[^0-9,]/g, "").replace(/(,.*),/g, "$1")',
                    ])
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return null;
                        // Format dengan koma sebagai pemisah desimal (format Indonesia)
                        $formatted = rtrim(rtrim(number_format($state, 2, ',', ''), '0'), ',');
                        return $formatted;
                    })
                    ->dehydrateStateUsing(function ($state) {
                        // Convert koma ke titik untuk database
                        return (float) str_replace(',', '.', $state);
                    })
                    ->rules(['regex:/^[0-9]+([,][0-9]{1,2})?$/', 'min:0', 'max:999']),
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
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return null;
                        // Format dengan koma sebagai pemisah desimal (format Indonesia)
                        $formatted = rtrim(rtrim(number_format($state, 2, ',', ''), '0'), ',');
                        return $formatted . '%';
                    }),
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
