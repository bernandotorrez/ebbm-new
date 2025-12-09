<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KantorSarResource\Pages;
use App\Filament\Resources\KantorSarResource\RelationManagers;
use App\Models\KantorSar;
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

class KantorSarResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = KantorSar::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Kantor SAR';

    protected static ?int $navigationSort = 6; // 6. Kantor SAR

    protected static ?string $slug = 'kantor-sar';

    public static function getModelLabel(): string
    {
        return 'Kantor SAR'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Kantor Sar';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kantor_sar')
                    ->label('Kantor SAR')
                    ->placeholder('Contoh: Kantor SAR Jakarta')
                    ->required()
                    ->maxLength(50)
                    ->rules(['required', 'string', 'max:50']),
                
                Forms\Components\Select::make('kota_id')
                    ->label('Kota')
                    ->relationship('kota', 'kota')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('Pilih Kota')
                    ->rules(['required', 'exists:ms_kota,kota_id']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kantor_sar')
                    ->label('Kantor SAR')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('kota.kota')
                    ->label('Kota')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
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
                        ->modalSubheading('Apakah kamu yakin ingin menghapus data yang dipilih?')
                        ->modalButton('Ya, Hapus Sekarang')
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                            $hasRelations = false;
                            $errorMessages = [];
                            
                            foreach ($records as $record) {
                                $userCount = $record->users()->count();
                                $alpalCount = $record->alpals()->count();
                                $sp3mCount = $record->sp3ms()->count();
                                $sp3kCount = $record->txSp3ks()->count();
                                $pemakaianCount = $record->pemakaians()->count();
                                
                                if ($userCount > 0 || $alpalCount > 0 || $sp3mCount > 0 || $sp3kCount > 0 || $pemakaianCount > 0) {
                                    $hasRelations = true;
                                    $relations = [];
                                    if ($userCount > 0) $relations[] = "{$userCount} user";
                                    if ($alpalCount > 0) $relations[] = "{$alpalCount} alut";
                                    if ($sp3mCount > 0) $relations[] = "{$sp3mCount} SP3M";
                                    if ($sp3kCount > 0) $relations[] = "{$sp3kCount} SP3K";
                                    if ($pemakaianCount > 0) $relations[] = "{$pemakaianCount} pemakaian";
                                    
                                    $errorMessages[] = "Kantor SAR {$record->kantor_sar} masih memiliki " . implode(', ', $relations) . " yang terkait.";
                                }
                            }
                            
                            if ($hasRelations) {
                                Notification::make()
                                    ->danger()
                                    ->title('Tidak dapat menghapus Kantor SAR')
                                    ->body('Beberapa Kantor SAR masih memiliki data terkait:<br>' . implode('<br>', $errorMessages))
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
            'index' => Pages\ListKantorSars::route('/'),
            'create' => Pages\CreateKantorSar::route('/create'),
            'edit' => Pages\EditKantorSar::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
