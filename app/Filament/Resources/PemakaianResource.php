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
                // Section 1: Info Readonly (di atas)
                Forms\Components\Section::make('Informasi Alut')
                    ->description('Data otomatis dari alut yang terdaftar')
                    ->schema([
                        // Hidden field untuk kantor_sar_id
                        Forms\Components\Hidden::make('kantor_sar_id')
                            ->required()
                            ->default(function () {
                                $user = Auth::user();
                                if ($user && $user->alpal_id) {
                                    $alpal = Alpal::find($user->alpal_id);
                                    return $alpal?->kantor_sar_id;
                                }
                                return null;
                            }),

                        // Alut - readonly
                        Forms\Components\Select::make('alpal_id')
                            ->relationship(
                                name: 'alpal',
                                titleAttribute: 'alpal',
                                modifyQueryUsing: function (Builder $query) {
                                    $user = Auth::user();
                                    if ($user && $user->alpal_id) {
                                        $query->where('alpal_id', $user->alpal_id);
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
                                return $user?->alpal_id;
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
                                        
                                        if ($alpal->golongan_bbm_id) {
                                            $bekal = \App\Models\Bekal::where('golongan_bbm_id', $alpal->golongan_bbm_id)->first();
                                            if ($bekal) {
                                                $set('bekal_id', $bekal->bekal_id);
                                            }
                                        }
                                    }
                                }
                            })
                            ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                if ($state) {
                                    $alpal = Alpal::with('kantorSar')->find($state);
                                    if ($alpal) {
                                        $set('kantor_sar_id', $alpal->kantor_sar_id);
                                        $set('kantor_sar_display', $alpal->kantorSar?->kantor_sar ?? '');
                                        $set('rob_info', number_format($alpal->rob, 0, ',', '.'));
                                        $set('kapasitas_info', number_format($alpal->kapasitas, 0, ',', '.'));
                                        
                                        if ($alpal->golongan_bbm_id) {
                                            $bekal = \App\Models\Bekal::where('golongan_bbm_id', $alpal->golongan_bbm_id)->first();
                                            if ($bekal) {
                                                $set('bekal_id', $bekal->bekal_id);
                                            }
                                        }
                                    }
                                } else {
                                    $set('kantor_sar_id', null);
                                    $set('kantor_sar_display', '');
                                    $set('rob_info', '');
                                    $set('kapasitas_info', '');
                                    $set('bekal_id', null);
                                }
                            }),

                        // Kantor SAR - readonly
                        Forms\Components\TextInput::make('kantor_sar_display')
                            ->label('Kantor SAR')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(function () {
                                $user = Auth::user();
                                if ($user && $user->alpal_id) {
                                    $alpal = Alpal::with('kantorSar')->find($user->alpal_id);
                                    return $alpal?->kantorSar?->kantor_sar ?? '';
                                }
                                return '';
                            }),

                        // Jenis Bahan Bakar - readonly
                        Forms\Components\Select::make('bekal_id')
                            ->relationship('bekal', 'bekal')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Jenis Bahan Bakar')
                            ->disabled()
                            ->dehydrated()
                            ->default(function () {
                                $user = Auth::user();
                                if ($user && $user->alpal_id) {
                                    $alpal = Alpal::find($user->alpal_id);
                                    if ($alpal && $alpal->golongan_bbm_id) {
                                        $bekal = \App\Models\Bekal::where('golongan_bbm_id', $alpal->golongan_bbm_id)->first();
                                        return $bekal?->bekal_id;
                                    }
                                }
                                return null;
                            }),

                        // ROB Saat Ini - readonly
                        Forms\Components\TextInput::make('rob_info')
                            ->label('ROB Saat Ini')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(function () {
                                $user = Auth::user();
                                if ($user && $user->alpal_id) {
                                    $alpal = Alpal::find($user->alpal_id);
                                    return $alpal ? number_format($alpal->rob, 0, ',', '.') : '';
                                }
                                return '';
                            })
                            ->extraAttributes([
                                'style' => 'font-weight: 600; color: #059669;'
                            ]),

                        // Kapasitas - readonly
                        Forms\Components\TextInput::make('kapasitas_info')
                            ->label('Kapasitas')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(function () {
                                $user = Auth::user();
                                if ($user && $user->alpal_id) {
                                    $alpal = Alpal::find($user->alpal_id);
                                    return $alpal ? number_format($alpal->kapasitas, 0, ',', '.') : '';
                                }
                                return '';
                            })
                            ->extraAttributes([
                                'style' => 'font-weight: 600; color: #3b82f6;'
                            ]),
                    ])
                    ->columns(2),

                // Section 2: Input Form (di bawah)
                Forms\Components\Section::make('Detail Pemakaian')
                    ->description('Masukkan informasi pemakaian bahan bakar')
                    ->schema([
                        // Tanggal
                        Forms\Components\DatePicker::make('tanggal_pakai')
                            ->label('Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection(true)
                            ->maxDate(now()),

                        // Qty
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
                                        
                                        if ($qty < 1) {
                                            $fail('Qty minimal 1.');
                                            return;
                                        }
                                        
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

                        // Data Kegiatan
                        Forms\Components\Select::make('data_kegiatan')
                            ->label('Data Kegiatan')
                            ->options([
                                'Rutin' => 'Rutin',
                                'Giat Lain' => 'Giat Lain',
                                'Operasi SAR' => 'Operasi SAR',
                            ])
                            ->required()
                            ->searchable()
                            ->native(false),

                        // Keterangan
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->required()
                            ->maxLength(1000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        
        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user) {
                // Filter khusus untuk ABK - hanya tampilkan pemakaian dari kapal mereka
                if ($user && $user->level->value === LevelUser::ABK->value && $user->alpal_id) {
                    $query->where('alpal_id', $user->alpal_id);
                }
                // Filter untuk Kansar - tampilkan pemakaian dari kantor SAR mereka
                elseif ($user && $user->level->value === LevelUser::KANSAR->value && $user->kantor_sar_id) {
                    $query->where('kantor_sar_id', $user->kantor_sar_id);
                }
                
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('kantorSar.kantor_sar')
                    ->label('Kantor SAR')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('alpal.alpal')
                    ->label('Alut')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('bekal.bekal')
                    ->label('Jenis Bahan Bakar')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_pakai')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_kegiatan')
                    ->label('Data Kegiatan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->searchable(),
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
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Konfirmasi Hapus Data')
                    ->modalDescription('Apakah kamu yakin ingin menghapus data ini? Qty akan dikembalikan ke ROB.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->before(function (Pemakaian $record) {
                        // Kembalikan qty ke ROB alpal
                        $alpal = Alpal::find($record->alpal_id);
                        if ($alpal) {
                            $alpal->rob += $record->qty;
                            $alpal->save();
                        }
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Berhasil')
                            ->body('Data pemakaian berhasil dihapus dan qty dikembalikan ke ROB.')
                    ),
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
            ])
            ->recordUrl(null);
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
        
        // Filter khusus untuk ABK - hanya tampilkan pemakaian dari kapal mereka
        if ($user && $user->level->value === LevelUser::ABK->value && $user->alpal_id) {
            $query->where('alpal_id', $user->alpal_id);
        }
        // Filter untuk Kansar - tampilkan pemakaian dari kantor SAR mereka
        elseif ($user && $user->level->value === LevelUser::KANSAR->value && $user->kantor_sar_id) {
            $query->where('kantor_sar_id', $user->kantor_sar_id);
        }
        
        return $query;
    }
}
