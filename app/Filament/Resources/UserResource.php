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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Kelola User';

    protected static ?int $navigationSort = 90;

    protected static ?string $slug = 'kelola-user';

    public static function getModelLabel(): string
    {
        return 'Kelola User'; // Singular name
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar User';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->level === 'admin'; // Hanya admin yang bisa melihat menu
    }

    public static function canAccess(array $parameters = []): bool
    {
        return Auth::user()?->level === 'admin'; // Hanya admin yang bisa mengakses
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kantor_sar_id')
                    ->relationship(name: 'kantorSar', titleAttribute: 'kantor_sar')
                    ->label('Kantor SAR')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->autocomplete(false)
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('Email Verified At')
                    ->default(null),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->autocomplete(false)
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('remember_token')
                    ->label('Remember Token')
                    ->maxLength(100),
                Forms\Components\Select::make('level')
                    ->options(LevelUser::values())
                    ->label('Level')
                    ->default('abk')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kantorSar.kantor_sar')
                    ->numeric()
                    ->label('Kantor SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'kanpus' => 'warning',
                        'kansar' => 'info',
                        'abk' => 'success',
                        default => 'gray',
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
                SelectFilter::make('kantor_sar_id')
                    ->label('Kantor Sar')
                    ->relationship('kantorSar', 'kantor_sar') // Relasi ke Golongan BBM
                    ->preload(),
                SelectFilter::make('level')
                    ->options(LevelUser::values())
                    ->label('Level'),
                // Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                ->label('Hapus')
                ->before(function (User $record) {
                    if ($record->level === 'admin') {
                        Notification::make()
                            ->title('Penghapusan Gagal')
                            ->body('User dengan level admin tidak bisa dihapus.')
                            ->danger()
                            ->send();

                        return false; // Mencegah penghapusan
                    }
                }),
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
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
        return parent::getEloquentQuery();
    }
}
