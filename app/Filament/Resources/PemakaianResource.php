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
                        // 1. Tanggal
                        Forms\Components\DatePicker::make('tanggal_pakai')
                            ->label('Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection(true)
                            ->maxDate(now()),

                        // 2. Alut - diambil dari user login (tx_alpal_id), hanya 1 option
                        Forms\Components\Select::make('alpal_id')
                            ->relationship(
                                name: 'alpal',
                                titleAttribute: 'alpal',
                                modifyQueryUsing: function (Builder $query) {
                                    $user = Auth::user();
                                    
                                    // Filter hanya alpal yang dimiliki user (dari tx_alpal_id)
                                    if ($user && $user->tx_alpal_id) {
                                        $query->where('alpal_id', $user->tx_alpal_id);
                                    }
                                    
                                    return $query;
                                }
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Alut')
                            ->live()
                            ->default(function () {
                                $user = Auth::user();
                                return $user?->tx_alpal_id;
                            })
                            ->disabled()
                            ->dehydrated()
                            ->afterStateHydrated(function (callable $set, $state) {
                                if ($state) {
                                    $alpal = Alpal::with('kantorSar')->find($state);
                                    if ($alpal) {
                                        $set('kantor_sar_id', $alpal->kantor_sar_id);
                                        $set('kantor_sar_display', $alpal->kantorSar?->kantor_sar ?? '');
                                        $set('rob_info', number_format($alpal->rob, 0, ',', '.'));
                                        $set('kapasitas_info', number_format($alpal->kapasitas, 0, ',', '.'));
                                    }
                                }
                            })
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                if ($state) {
                                    $alpal = Alpal::with('kantorSar')->find($state);
                                    if ($alpal) {
                                        // Set kantor_sar_id otomatis dari alpal (di belakang layar)
                                        $set('kantor_sar_id', $alpal->kantor_sar_id);
                                        $set('kantor_sar_display', $alpal->kantorSar?->kantor_sar ?? '');
                                        $set('rob_info', number_format($alpal->rob, 0, ',', '.'));
                                        $set('kapasitas_info', number_format($alpal->kapasitas, 0, ',', '.'));
                                    }
                                } else {
                                    $set('kantor_sar_id', null);
                                    $set('kantor_sar_display', '');
                                    $set('rob_info', '');
                                    $set('kapasitas_info', '');
                                }
                            }),

                        // 3. Kegiatan (Keterangan)
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Kegiatan')
                            ->required()
                            ->maxLength(1000)
                            ->rows(3),

                        // 4. Kantor SAR - otomatis terisi dari alpal (readonly, tapi data tetap dikirim)
                        Forms\Components\Hidden::make('kantor_sar_id')
                            ->required()
                            ->default(function () {
                                $user = Auth::user();
                                if ($user && $user->tx_alpal_id) {
                                    $alpal = Alpal::find($user->tx_alpal_id);
                                    return $alpal?->kantor_sar_id;
                                }
                                return null;
                            }),
                        
                        Forms\Components\TextInput::make('kantor_sar_display')
                            ->label('Kantor SAR')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(function () {
                                $user = Auth::user();
                                if ($user && $user->tx_alpal_id) {
                                    $alpal = Alpal::with('kantorSar')->find($user->tx_alpal_id);
                                    return $alpal?->kantorSar?->kantor_sar ?? '';
                                }
                                return '';
                            })
                            ->extraAttributes([
                                'style' => 'font-weight: 500;'
                            ]),

                        // 5. Qty
                        Forms\Components\TextInput::make('qty')
                            ->required()
                            ->label('Qty')
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

                        // 6. Jenis Bahan Bakar (Bekal)
                        Forms\Components\Select::make('bekal_id')
                            ->relationship('bekal', 'bekal')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Jenis Bahan Bakar'),

                        // Info tambahan
                        Forms\Components\TextInput::make('rob_info')
                            ->label('ROB Saat Ini')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(function () {
                                $user = Auth::user();
                                if ($user && $user->tx_alpal_id) {
                                    $alpal = Alpal::find($user->tx_alpal_id);
                                    return $alpal ? number_format($alpal->rob, 0, ',', '.') : '';
                                }
                                return '';
                            })
                            ->extraAttributes([
                                'style' => 'font-weight: 600; color: #059669;'
                            ]),

                        Forms\Components\TextInput::make('kapasitas_info')
                            ->label('Kapasitas')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(function () {
                                $user = Auth::user();
                                if ($user && $user->tx_alpal_id) {
                                    $alpal = Alpal::find($user->tx_alpal_id);
                                    return $alpal ? number_format($alpal->kapasitas, 0, ',', '.') : '';
                                }
                                return '';
                            })
                            ->extraAttributes([
                                'style' => 'font-weight: 600; color: #3b82f6;'
                            ]),
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
