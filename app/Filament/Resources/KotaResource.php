<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KotaResource\Pages;
use App\Filament\Resources\KotaResource\RelationManagers;
use App\Models\Kota;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Traits\RoleBasedResourceAccess;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class KotaResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = Kota::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Kota';

    protected static ?int $navigationSort = 5; // 5. Kota

    protected static ?string $slug = 'kota';

    public static function getModelLabel(): string
    {
        return 'Kota'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Kota'; // Custom title for the table
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kota')
                    ->required()
                    ->placeholder('Contoh: Jakarta')
                    ->maxLength(50),
                Forms\Components\Select::make('wilayah_id')
                    ->label('Wilayah')
                    ->options(\App\Models\Wilayah::all()->pluck('wilayah_ke', 'wilayah_id'))
                    ->searchable()
                    ->placeholder('Pilih Wilayah')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kota')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('wilayah.wilayah_ke')
                    ->label('Wilayah')
                    ->searchable()
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('wilayah_id')
                    ->relationship('wilayah', 'wilayah_ke')
                    ->placeholder('Pilih Wilayah'),
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
                        ->modalSubheading('Apakah kamu yakin ingin menghapus data yang dipilih?')
                        ->modalButton('Ya, Hapus Sekarang')
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                            $hasRelations = false;
                            $errorMessages = [];
                            
                            foreach ($records as $record) {
                                // Hitung child yang aktif (is_active = '1')
                                $tbbmCount = $record->tbbms()->count();
                                $kantorSarCount = $record->kantorSars()->count();
                                $doCount = $record->deliveryOrders()->count();
                                
                                if ($tbbmCount > 0 || $kantorSarCount > 0 || $doCount > 0) {
                                    $hasRelations = true;
                                    $relations = [];
                                    if ($tbbmCount > 0) $relations[] = "{$tbbmCount} TBBM";
                                    if ($kantorSarCount > 0) $relations[] = "{$kantorSarCount} Kantor SAR";
                                    if ($doCount > 0) $relations[] = "{$doCount} Delivery Order";
                                    
                                    $errorMessages[] = "Kota {$record->kota} masih memiliki " . implode(', ', $relations) . " yang terkait.";
                                }
                            }
                            
                            if ($hasRelations) {
                                Notification::make()
                                    ->danger()
                                    ->title('Tidak dapat menghapus kota')
                                    ->body('Beberapa kota masih memiliki data terkait:<br>' . implode('<br>', $errorMessages))
                                    ->persistent()
                                    ->send();
                                
                                $action->cancel();
                            }
                        }),
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
            'index' => Pages\ListKotas::route('/'),
            'create' => Pages\CreateKota::route('/create'),
            'edit' => Pages\EditKota::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
