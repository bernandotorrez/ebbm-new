<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WilayahResource\Pages;
use App\Filament\Resources\WilayahResource\RelationManagers;
use App\Models\Wilayah;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\RoleBasedResourceAccess;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class WilayahResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = Wilayah::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Wilayah';

    protected static ?int $navigationSort = 4; // 4. Wilayah

    protected static ?string $slug = 'wilayah';

    public static function getModelLabel(): string
    {
        return 'Wilayah'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Wilayah'; // Custom title for the table
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('wilayah_ke')
                    ->label('Wilayah Ke')
                    ->placeholder('Contoh: 1')
                    ->required()
                    ->extraInputAttributes([
                        'type' => 'text',
                        'maxlength' => '1',
                        'oninput' => 'this.value = this.value.replace(/[^1-9]/g, "")',
                    ])
                    ->rules(['required', 'regex:/^[1-9]$/']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('wilayah_ke')
                    ->label('Wilayah Ke')
                    ->numeric()
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Konfirmasi Hapus Data')
                    ->modalSubheading('Apakah kamu yakin ingin menghapus data ini? ')
                    ->modalButton('Ya, Hapus')
                    ->before(function (Tables\Actions\DeleteAction $action, Model $record) {
                        // Cek apakah ada kota yang terkait
                        $kotaCount = $record->kotas()->count();
                        
                        if ($kotaCount > 0) {
                            Notification::make()
                                ->danger()
                                ->title('Tidak dapat menghapus wilayah')
                                ->body("Wilayah ini masih memiliki {$kotaCount} kota yang terkait. Hapus kota terlebih dahulu.")
                                ->persistent()
                                ->send();
                            
                            $action->cancel();
                        }
                        
                        // Cek apakah ada harga bekal yang terkait
                        $hargaBekalCount = \App\Models\HargaBekal::where('wilayah_id', $record->wilayah_id)->count();
                        
                        if ($hargaBekalCount > 0) {
                            Notification::make()
                                ->danger()
                                ->title('Tidak dapat menghapus wilayah')
                                ->body("Wilayah ini masih memiliki {$hargaBekalCount} harga bekal yang terkait. Hapus harga bekal terlebih dahulu.")
                                ->persistent()
                                ->send();
                            
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->modalHeading('Konfirmasi Hapus Data')
                        ->modalSubheading('Apakah kamu yakin ingin menghapus data yang dipilih? ')
                        ->modalButton('Ya, Hapus Sekarang')
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                            $hasRelations = false;
                            $errorMessages = [];
                            
                            foreach ($records as $record) {
                                $kotaCount = $record->kotas()->count();
                                $hargaBekalCount = \App\Models\HargaBekal::where('wilayah_id', $record->wilayah_id)->count();
                                
                                if ($kotaCount > 0 || $hargaBekalCount > 0) {
                                    $hasRelations = true;
                                    $errorMessages[] = "Wilayah {$record->wilayah_ke} masih memiliki " . 
                                        ($kotaCount > 0 ? "{$kotaCount} kota" : "");
                                }
                            }
                            
                            if ($hasRelations) {
                                Notification::make()
                                    ->danger()
                                    ->title('Tidak dapat menghapus wilayah')
                                    ->body('Beberapa wilayah masih memiliki data terkait:<br>' . implode('<br>', $errorMessages))
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
            'index' => Pages\ListWilayahs::route('/'),
            'create' => Pages\CreateWilayah::route('/create'),
            'edit' => Pages\EditWilayah::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}