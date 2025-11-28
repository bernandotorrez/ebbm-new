<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PemakaianResource\Pages;
use App\Models\Pemakaian;
use App\Models\Alpal;
use App\Enums\LevelUser;
use App\Traits\RoleBasedResourceAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PemakaianResource extends Resource
{
    use RoleBasedResourceAccess;
    protected static ?string $model = Pemakaian::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pemakaian';

    protected static ?int $navigationSort = 3;

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
                            ->relationship(
                                name: 'kantorSar',
                                titleAttribute: 'kantor_sar',
                                modifyQueryUsing: function (Builder $query) {
                                    $user = Auth::user();
                                    
                                    // Filter untuk KANSAR dan ABK
                                    if ($user && $user->kantor_sar_id && 
                                        in_array($user->level->value, [LevelUser::KANSAR->value, LevelUser::ABK->value])) {
                                        $query->where('kantor_sar_id', $user->kantor_sar_id);
                                    }
                                    
                                    return $query;
                                }
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Kantor SAR')
                            ->live()
                            ->default(function () {
                                $user = Auth::user();
                                
                                // Set default untuk KANSAR dan ABK
                                if ($user && $user->kantor_sar_id && 
                                    in_array($user->level->value, [LevelUser::KANSAR->value, LevelUser::ABK->value])) {
                                    return $user->kantor_sar_id;
                                }
                                
                                return null;
                            })
                            ->disabled(function () {
                                $user = Auth::user();
                                
                                // Disable untuk KANSAR dan ABK
                                return $user && $user->kantor_sar_id && 
                                    in_array($user->level->value, [LevelUser::KANSAR->value, LevelUser::ABK->value]);
                            })
                            ->dehydrated()
                            ->afterStateUpdated(function (callable $set) {
                                // Reset alpal_id saat kantor_sar_id berubah
                                $set('alpal_id', null);
                                $set('rob_info', '');
                                $set('kapasitas_info', '');
                            }),

                        Forms\Components\Select::make('alpal_id')
                            ->relationship(
                                name: 'alpal',
                                titleAttribute: 'alpal',
                                modifyQueryUsing: function (Builder $query, callable $get) {
                                    $user = Auth::user();
                                    $kantorSarId = $get('kantor_sar_id');
                                    
                                    // Filter berdasarkan kantor_sar_id yang dipilih di form
                                    if ($kantorSarId) {
                                        $query->where('kantor_sar_id', $kantorSarId);
                                    }
                                    // Atau filter untuk KANSAR dan ABK jika belum ada kantor_sar_id
                                    elseif ($user && $user->kantor_sar_id && 
                                        in_array($user->level->value, [LevelUser::KANSAR->value, LevelUser::ABK->value])) {
                                        $query->where('kantor_sar_id', $user->kantor_sar_id);
                                    }
                                    
                                    return $query;
                                }
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Alut')
                            ->live()
                            ->afterStateHydrated(function (callable $set, $state) {
                                if ($state) {
                                    $alpal = Alpal::find($state);
                                    if ($alpal) {
                                        $set('rob_info', number_format($alpal->rob, 0, ',', '.'));
                                        $set('kapasitas_info', number_format($alpal->kapasitas, 0, ',', '.'));
                                    }
                                }
                            })
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                if ($state) {
                                    $alpal = Alpal::find($state);
                                    if ($alpal) {
                                        $set('rob_info', number_format($alpal->rob, 0, ',', '.'));
                                        $set('kapasitas_info', number_format($alpal->kapasitas, 0, ',', '.'));
                                    }
                                } else {
                                    $set('rob_info', '');
                                    $set('kapasitas_info', '');
                                }
                            }),

                        Forms\Components\TextInput::make('rob_info')
                            ->label('ROB Saat Ini')
                            ->disabled()
                            ->dehydrated(false)
                            ->extraAttributes([
                                'style' => 'font-weight: 600; color: #059669;'
                            ]),

                        Forms\Components\Select::make('bekal_id')
                            ->relationship('bekal', 'bekal')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Bekal'),

                        Forms\Components\TextInput::make('kapasitas_info')
                            ->label('Kapasitas')
                            ->disabled()
                            ->dehydrated(false)
                            ->extraAttributes([
                                'style' => 'font-weight: 600; color: #3b82f6;'
                            ]),

                        Forms\Components\DatePicker::make('tanggal_pakai')
                            ->required()
                            ->maxDate(now())
                            ->label('Tanggal Pakai'),

                        Forms\Components\TextInput::make('qty')
                            ->required()
                            ->label('Quantity')
                            ->inputMode('numeric')
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (callable $get, callable $set, $state, $livewire) {
                                $qty = (int) str_replace(['.', ',', ' '], '', $state);
                                $alpalId = $get('alpal_id');
                                
                                if ($alpalId && $qty > 0) {
                                    $alpal = Alpal::find($alpalId);
                                    if ($alpal) {
                                        $availableRob = $alpal->rob;
                                        
                                        // Jika sedang edit, tambahkan qty lama ke ROB untuk validasi
                                        if (isset($livewire->record) && $livewire->record->alpal_id == $alpalId) {
                                            $availableRob += $livewire->record->qty;
                                        }
                                        
                                        if ($qty > $availableRob) {
                                            $set('qty_error', "Qty melebihi ROB yang tersedia (" . number_format($availableRob, 0, ',', '.') . ")");
                                        } else {
                                            $set('qty_error', null);
                                        }
                                    }
                                }
                            })
                            ->extraInputAttributes([
                                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                            ])
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                            ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                            ->minValue(1)
                            ->maxValue(99999)
                            ->helperText(fn ($get) => $get('qty_error') ? 
                                new \Illuminate\Support\HtmlString('<span style="color: #ef4444; font-weight: 600;">' . $get('qty_error') . '</span>') 
                                : null
                            )
                            ->rules([
                                function ($get, $livewire) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $livewire) {
                                        $qty = (int) str_replace(['.', ',', ' '], '', $value);
                                        $alpalId = $get('alpal_id');
                                        
                                        if ($alpalId && $qty > 0) {
                                            $alpal = Alpal::find($alpalId);
                                            if ($alpal) {
                                                $availableRob = $alpal->rob;
                                                
                                                if (isset($livewire->record) && $livewire->record->alpal_id == $alpalId) {
                                                    $availableRob += $livewire->record->qty;
                                                }
                                                
                                                if ($qty > $availableRob) {
                                                    $fail("Qty ({$qty}) melebihi ROB yang tersedia ({$availableRob}).");
                                                }
                                            }
                                        }
                                    };
                                },
                            ]),

                        Forms\Components\Textarea::make('keterangan')
                            ->required()
                            ->maxLength(1000)
                            ->rows(3)
                            ->label('Keterangan'),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        
        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user) {
                // Apply user-level filtering for KANSAR and ABK users
                if ($user && $user->kantor_sar_id && 
                    in_array($user->level->value, [LevelUser::KANSAR->value, LevelUser::ABK->value])) {
                    $query->where('kantor_sar_id', $user->kantor_sar_id);
                }
                
                return $query;
            })
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
                    ->label('Alut'),
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
                        ->modalButton('Ya, Hapus Sekarang')
                        ->before(function ($records) {
                            // Kembalikan ROB untuk setiap pemakaian yang dihapus
                            foreach ($records as $record) {
                                $alpal = Alpal::find($record->alpal_id);
                                if ($alpal) {
                                    $alpal->rob += $record->qty;
                                    $alpal->save();
                                }
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
            'index' => Pages\ListPemakaians::route('/'),
            'create' => Pages\CreatePemakaian::route('/create'),
            'edit' => Pages\EditPemakaian::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
            
        $user = Auth::user();
        
        // Apply user-level filtering for KANSAR and ABK users
        if ($user && $user->kantor_sar_id && 
            in_array($user->level->value, [LevelUser::KANSAR->value, LevelUser::ABK->value])) {
            $query->where('kantor_sar_id', $user->kantor_sar_id);
        }
        
        return $query;
    }
}
