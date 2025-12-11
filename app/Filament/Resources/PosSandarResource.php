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
use App\Traits\RoleBasedResourceAccess;
use Filament\Notifications\Notification;

class PosSandarResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = PosSandar::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Pos Sandar';

    protected static ?int $navigationSort = 8; // 8. Pos Sandar

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
                Forms\Components\Select::make('kantor_sar_id')
                    ->label('Kantor SAR')
                    ->relationship('kantorSar', 'kantor_sar')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('Pilih Kantor SAR'),
                Forms\Components\TextInput::make('pos_sandar')
                    ->label('Pos Sandar')
                    ->placeholder('Contoh: Pos Sandar Jakarta')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kantorSar.kantor_sar')
                    ->label('Kantor SAR')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pos_sandar')
                    ->label('Pos Sandar')
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
                                $alpalCount = $record->alpals()->count();
                                
                                if ($alpalCount > 0) {
                                    $hasRelations = true;
                                    $errorMessages[] = "Pos Sandar {$record->pos_sandar} masih memiliki {$alpalCount} Alut yang terkait.";
                                }
                            }
                            
                            if ($hasRelations) {
                                Notification::make()
                                    ->danger()
                                    ->title('Tidak dapat menghapus Pos Sandar')
                                    ->body('Beberapa Pos Sandar masih memiliki data terkait:<br>' . implode('<br>', $errorMessages))
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
