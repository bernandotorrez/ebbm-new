<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use App\Enums\LevelUser;
use Filament\Notifications\Notification;
use App\Traits\RoleBasedResourceAccess;

class UserResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?int $navigationSort = 90;

    protected static ?string $slug = 'pengguna';

    public static function getModelLabel(): string
    {
        return 'Pengguna'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Pengguna';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('level')
                    ->options(function () {
                        $levels = LevelUser::values();
                        // Remove Admin from options
                        unset($levels[LevelUser::ADMIN->value]);
                        return $levels;
                    })
                    ->label('Level')
                    ->placeholder('Pilih Level')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (callable $set, $state) {
                        // Reset kantor_sar_id dan alpal_id saat level berubah
                        $set('kantor_sar_id', null);
                        $set('alpal_id', null);
                    }),
                
                // Conditional field: Kantor SAR untuk non-ABK, Alut untuk ABK
                Forms\Components\Select::make('kantor_sar_id')
                    ->relationship(name: 'kantorSar', titleAttribute: 'kantor_sar')
                    ->label('Kantor SAR')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (callable $get) => $get('level') !== LevelUser::ABK->value),
                
                Forms\Components\Select::make('alpal_id')
                    ->relationship(name: 'alpal', titleAttribute: 'alpal')
                    ->label('Alut')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (callable $get) => $get('level') === LevelUser::ABK->value),
                
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(200)
                    ->unique(ignoreRecord: true),
                
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->autocomplete(false)
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->autocomplete('new-password')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255)
                    ->helperText(fn (string $operation): ?string => 
                        $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password' : null
                    )
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('level')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        LevelUser::ADMIN => 'Admin',
                        LevelUser::KANPUS => 'Kantor Pusat',
                        LevelUser::KANSAR => 'Kantor SAR',
                        LevelUser::ABK => 'ABK',
                        default => $state,
                    })
                    ->color(fn ($state): string => match ($state) {
                        LevelUser::ADMIN => 'danger',
                        LevelUser::KANPUS => 'warning',
                        LevelUser::KANSAR => 'info',
                        LevelUser::ABK => 'success',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('kantorSar.kantor_sar')
                    ->label('Kantor SAR')
                    ->sortable()
                    ->visible(fn ($record) => $record && $record->level !== LevelUser::ABK),
                
                Tables\Columns\TextColumn::make('alpal.alpal')
                    ->label('Alut')
                    ->sortable()
                    ->visible(fn ($record) => $record && $record->level === LevelUser::ABK),
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

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
                SelectFilter::make('level')
                    ->options(LevelUser::values())
                    ->label('Level'),
                SelectFilter::make('kantor_sar_id')
                    ->label('Kantor SAR')
                    ->relationship('kantorSar', 'kantor_sar')
                    ->preload(),
                SelectFilter::make('alpal_id')
                    ->label('Alut')
                    ->relationship('alpal', 'alpal')
                    ->preload(),
                // Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (User $record): bool => $record && $record->level !== LevelUser::ADMIN)
                    ->before(function (User $record) {
                        if ($record && $record->level === LevelUser::ADMIN) {
                            Notification::make()
                                ->title('Tidak Dapat Menghapus!')
                                ->body('User dengan level Admin tidak dapat dihapus.')
                                ->danger()
                                ->send();
                            return false;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->modalHeading('Konfirmasi Hapus Data')
                        ->modalSubheading('Apakah kamu yakin ingin menghapus data yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalButton('Ya, Hapus Sekarang')
                        ->before(function ($records) {
                            // Check if any record is Admin
                            $hasAdmin = $records->contains(function ($record) {
                                return $record && $record->level === LevelUser::ADMIN;
                            });
                            
                            if ($hasAdmin) {
                                Notification::make()
                                    ->title('Tidak Dapat Menghapus!')
                                    ->body('Tidak dapat menghapus user dengan level Admin. Silakan hapus user non-Admin saja.')
                                    ->danger()
                                    ->send();
                                return false;
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('level', '!=', LevelUser::ADMIN->value);
    }
}