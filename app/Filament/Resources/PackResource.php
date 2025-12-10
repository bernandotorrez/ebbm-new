<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackResource\Pages;
use App\Filament\Resources\PackResource\RelationManagers;
use App\Models\Pack;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\RoleBasedResourceAccess;
use Filament\Notifications\Notification;

class PackResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = Pack::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Pack';

    protected static ?int $navigationSort = 10; // 10. Pack

    protected static ?string $slug = 'pack';

    public static function getModelLabel(): string
    {
        return 'Pack'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Pack'; // Custom title for the table
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_pack')
                    ->label('Nama Pack')
                    ->placeholder('Contoh: Drum')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_pack')
                    ->label('Nama Pack')
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
                Tables\Filters\TrashedFilter::make(),
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
                                // Hanya hitung child yang aktif (belum soft deleted)
                                $pelumasCount = $record->pelumas()->whereNull('deleted_at')->count();
                                $kemasanCount = $record->kemasans()->whereNull('deleted_at')->count();
                                
                                if ($pelumasCount > 0 || $kemasanCount > 0) {
                                    $hasRelations = true;
                                    $relations = [];
                                    if ($pelumasCount > 0) $relations[] = "{$pelumasCount} pelumas";
                                    if ($kemasanCount > 0) $relations[] = "{$kemasanCount} kemasan";
                                    
                                    $errorMessages[] = "Pack {$record->nama_pack} masih memiliki " . implode(', ', $relations) . " yang terkait.";
                                }
                            }
                            
                            if ($hasRelations) {
                                Notification::make()
                                    ->danger()
                                    ->title('Tidak dapat menghapus Pack')
                                    ->body('Beberapa Pack masih memiliki data terkait:<br>' . implode('<br>', $errorMessages))
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
            'index' => Pages\ListPacks::route('/'),
            'create' => Pages\CreatePack::route('/create'),
            'edit' => Pages\EditPack::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
