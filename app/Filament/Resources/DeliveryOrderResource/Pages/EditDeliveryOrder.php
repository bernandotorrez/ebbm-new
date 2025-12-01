<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use App\Models\DeliveryOrder;
use App\Models\Sp3m;
use App\Models\Tbbm;
use App\Models\HargaBekal;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryOrder extends EditRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        $user = auth()->user();
        $isKanpus = $user && $user->level->value === 'kanpus';
        
        // Jika Kanpus, redirect ke list (karena edit via modal, bukan halaman)
        if ($isKanpus) {
            Notification::make()
                ->title('Info')
                ->body('Silakan gunakan tombol Ubah di tabel untuk mengedit Harga BBM.')
                ->info()
                ->send();
            
            redirect($this->getResource()::getUrl('index'))->send();
            exit;
        }
        
        // Jika Kansar/ABK, hanya boleh akses DO terbaru
        $latestDo = DeliveryOrder::orderBy('tanggal_do', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Jika bukan DO terbaru, redirect ke list dengan notifikasi
        if (!$latestDo || $latestDo->do_id !== $this->record->do_id) {
            Notification::make()
                ->title('Akses Ditolak!')
                ->body('Hanya Delivery Order terbaru yang dapat diubah.')
                ->danger()
                ->send();
            
            // Redirect dan stop execution
            redirect($this->getResource()::getUrl('index'))->send();
            exit;
        }
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan'),
            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load relasi untuk ditampilkan di form
        $record = $this->record;
        $record->load(['sp3m.alpal', 'sp3m.kantorSar', 'tbbm']);
        
        return $data;
    }
    
    public function form(Form $form): Form
    {
        $user = auth()->user();
        $isKanpus = $user && $user->level->value === 'kanpus';
        $isKansar = $user && in_array($user->level->value, ['kansar', 'abk']);
        
        // Jika Kanpus, gunakan form khusus (hanya edit harga BBM)
        if ($isKanpus) {
            return $this->getKanpusForm($form);
        }
        
        // Jika Kansar/ABK, gunakan form khusus (hanya edit beberapa field)
        if ($isKansar) {
            return $this->getKansarForm($form);
        }
        
        // Default form (untuk admin atau level lain)
        return DeliveryOrderResource::form($form);
    }
    
    protected function getKanpusForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('nomor_sp3m_display')
                            ->label('Nomor SP3M')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $record->load('sp3m');
                                    $component->state($record->sp3m->nomor_sp3m ?? '-');
                                }
                            }),
                        
                        Forms\Components\TextInput::make('alut_display')
                            ->label('Alut')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $record->load('sp3m.alpal');
                                    $component->state($record->sp3m->alpal->alpal ?? '-');
                                }
                            }),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('tahun_anggaran_display')
                            ->label('Tahun Anggaran (TA)')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $component->state($record->tahun_anggaran ?? '-');
                                }
                            }),
                        
                        Forms\Components\TextInput::make('kantor_sar_display')
                            ->label('Kantor SAR')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $record->load('sp3m.kantorSar');
                                    $component->state($record->sp3m->kantorSar->kantor_sar ?? '-');
                                }
                            }),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('nomor_do_display')
                            ->label('Nomor DO')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $component->state($record->nomor_do ?? '-');
                                }
                            }),
                        
                        Forms\Components\TextInput::make('qty_display')
                            ->label('Qty')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $component->state(number_format($record->qty, 0, ',', '.'));
                                }
                            }),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('tanggal_do_display')
                            ->label('Tanggal')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record && $record->tanggal_do) {
                                    $component->state(\Carbon\Carbon::parse($record->tanggal_do)->format('d/m/Y'));
                                }
                            }),
                        
                        Forms\Components\TextInput::make('tbbm_display')
                            ->label('TBBM/DPPU')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $record->load('tbbm');
                                    $component->state($record->tbbm->depot ?? '-');
                                }
                            }),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('harga_bekal_id')
                            ->label('Harga BBM')
                            ->required()
                            ->options(function ($record) {
                                if (!$record || !$record->bekal_id || !$record->kota_id) {
                                    return [];
                                }
                                
                                return HargaBekal::where('bekal_id', $record->bekal_id)
                                    ->where('kota_id', $record->kota_id)
                                    ->orderBy('tanggal_update', 'desc')
                                    ->limit(5)
                                    ->get()
                                    ->mapWithKeys(function ($item) {
                                        $tanggal = $item->tanggal_update ? \Carbon\Carbon::parse($item->tanggal_update)->format('d/m/Y') : '-';
                                        $harga = 'Rp ' . number_format($item->harga, 0, ',', '.');
                                        return [$item->harga_bekal_id => "{$tanggal} - {$harga}"];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->helperText('Pilih harga BBM dari 5 data terbaru berdasarkan tanggal update'),
                        
                        Forms\Components\Placeholder::make('spacer')
                            ->label('')
                            ->content(''),
                    ]),
            ]);
    }
    
    protected function getKansarForm(Form $form): Form
    {
        $record = $this->record;
        $record->load(['sp3m.alpal', 'sp3m.kantorSar', 'sp3m.bekal', 'tbbm']);
        
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        // Nomor SP3M - Readonly
                        Forms\Components\TextInput::make('nomor_sp3m_display')
                            ->label('Nomor SP3M')
                            ->disabled()
                            ->dehydrated(false)
                            ->default($record->sp3m->nomor_sp3m ?? '-'),
                        
                        // Alut - Editable (via select SP3M alpal)
                        Forms\Components\Select::make('sp3m_id')
                            ->label('Alut')
                            ->relationship(name: 'sp3m', titleAttribute: 'nomor_sp3m')
                            ->options(function () use ($record) {
                                // Get SP3M with same bekal_id and kantor_sar_id
                                return Sp3m::where('bekal_id', $record->sp3m->bekal_id)
                                    ->where('kantor_sar_id', $record->sp3m->kantor_sar_id)
                                    ->where('sisa_qty', '>', 0)
                                    ->with('alpal')
                                    ->get()
                                    ->mapWithKeys(function ($sp3m) {
                                        $alut = $sp3m->alpal->alpal ?? '-';
                                        return [$sp3m->sp3m_id => "{$sp3m->nomor_sp3m} - {$alut}"];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) {
                                    $sp3m = Sp3m::with(['alpal'])->find($state);
                                    if ($sp3m) {
                                        $set('sisa_qty_info', number_format($sp3m->sisa_qty, 0, ',', '.'));
                                    }
                                }
                            }),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        // Tahun Anggaran - Readonly
                        Forms\Components\TextInput::make('tahun_anggaran')
                            ->label('Tahun Anggaran (TA)')
                            ->disabled()
                            ->dehydrated(true)
                            ->default($record->tahun_anggaran),
                        
                        // Kantor SAR - Readonly
                        Forms\Components\TextInput::make('kantor_sar_display')
                            ->label('Kantor SAR')
                            ->disabled()
                            ->dehydrated(false)
                            ->default($record->sp3m->kantorSar->kantor_sar ?? '-'),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        // Sisa Qty SP3M - Readonly
                        Forms\Components\TextInput::make('sisa_qty_info')
                            ->label('Sisa Qty SP3M')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(number_format($record->sp3m->sisa_qty, 0, ',', '.'))
                            ->extraAttributes([
                                'style' => 'font-weight: 600; color: #d97706;'
                            ]),
                        
                        // Jenis Bahan Bakar - Readonly
                        Forms\Components\TextInput::make('jenis_bahan_bakar_display')
                            ->label('Jenis Bahan Bakar')
                            ->disabled()
                            ->dehydrated(false)
                            ->default($record->sp3m->bekal->bekal ?? '-'),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        // Nomor DO - Readonly
                        Forms\Components\TextInput::make('nomor_do')
                            ->label('Nomor DO')
                            ->disabled()
                            ->dehydrated(true)
                            ->default($record->nomor_do),
                        
                        // Qty - Editable
                        Forms\Components\TextInput::make('qty')
                            ->required()
                            ->label('Qty')
                            ->inputMode('numeric')
                            ->extraInputAttributes([
                                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")'
                            ])
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                            ->dehydrateStateUsing(fn ($state) => (int) str_replace(['.', ',', ' '], '', $state))
                            ->minValue(1)
                            ->rules(['min:1'])
                            ->live(),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        // Tanggal - Editable
                        Forms\Components\DatePicker::make('tanggal_do')
                            ->label('Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection(true),
                        
                        // TBBM - Editable
                        Forms\Components\Select::make('tbbm_id')
                            ->relationship(name: 'tbbm', titleAttribute: 'depot')
                            ->label('TBBM/DPPU')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                
                Forms\Components\Grid::make(2)
                    ->schema([
                        // Lampiran - Editable
                        Forms\Components\FileUpload::make('file_upload_do')
                            ->label('Lampiran')
                            ->disk('public')
                            ->directory('delivery-order')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->required(),
                        
                        Forms\Components\Placeholder::make('spacer')
                            ->label('')
                            ->content(''),
                    ]),
            ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        $isKanpus = $user && $user->level->value === 'kanpus';
        
        // Jika Kanpus, hanya simpan harga_bekal_id
        if ($isKanpus) {
            $allowedFields = ['harga_bekal_id'];
            
            $filteredData = [];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $filteredData[$field] = $data[$field];
                }
            }
            
            return $filteredData;
        }
        
        // Jika Kansar/ABK, proses data seperti biasa
        // Clean numeric fields
        if (isset($data['qty'])) {
            $data['qty'] = (int) preg_replace('/[^\d]/', '', $data['qty']);
        }
        
        // Get bekal_id from SP3M
        $sp3mId = $data['sp3m_id'] ?? null;
        if ($sp3mId) {
            $sp3m = Sp3m::find($sp3mId);
            if ($sp3m) {
                $data['bekal_id'] = $sp3m->bekal_id;
            }
        }
        
        // Get kota_id from TBBM
        $tbbmId = $data['tbbm_id'] ?? null;
        if ($tbbmId) {
            $tbbm = Tbbm::find($tbbmId);
            if ($tbbm) {
                $data['kota_id'] = $tbbm->kota_id;
            }
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to the list page after update
        return $this->getResource()::getUrl('index');
    }
    
    protected function afterSave(): void
    {
        // Do nothing - let Filament handle redirect naturally
    }

    protected function beforeSave(): void
    {
        $user = auth()->user();
        $isKanpus = $user && $user->level->value === 'kanpus';
        
        // Jika Kanpus, hanya validasi harga_bekal_id
        if ($isKanpus) {
            $hargaBekalId = $this->data['harga_bekal_id'] ?? null;
            
            if (!$hargaBekalId) {
                Notification::make()
                    ->title('Error!')
                    ->body('Harga BBM harus dipilih.')
                    ->danger()
                    ->send();
                $this->halt();
            }
            
            // Validasi apakah harga_bekal_id valid untuk bekal_id dan kota_id DO ini
            $hargaBekal = HargaBekal::where('harga_bekal_id', $hargaBekalId)
                ->where('bekal_id', $this->record->bekal_id)
                ->where('kota_id', $this->record->kota_id)
                ->first();
            
            if (!$hargaBekal) {
                Notification::make()
                    ->title('Error!')
                    ->body('Harga BBM yang dipilih tidak valid untuk DO ini.')
                    ->danger()
                    ->send();
                $this->halt();
            }
            
            return;
        }
        
        // Jika Kansar/ABK, validasi seperti biasa (qty, sp3m, dll)
        $doId = $this->record->do_id;
        $sp3mId = $this->data['sp3m_id'] ?? null;
        $newQty = (int) preg_replace('/[^\d]/', '', $this->data['qty'] ?? 0);
        $oldQty = $this->record->qty;
        
        // Validasi sisa_qty di SP3M
        $sp3m = Sp3m::with(['alpal', 'bekal'])->find($sp3mId);
        
        if (!$sp3m) {
            Notification::make()
                ->title('Error!')
                ->body('SP3M tidak ditemukan.')
                ->danger()
                ->send();
            $this->halt();
        }
        
        // Hitung selisih qty
        $qtyDiff = $newQty - $oldQty;
        
        // Cek apakah sisa_qty mencukupi untuk perubahan
        if ($sp3m->sisa_qty < $qtyDiff) {
            $newQtyFormatted = number_format($newQty, 0, ',', '.');
            $availableQty = $sp3m->sisa_qty + $oldQty;
            $availableQtyFormatted = number_format($availableQty, 0, ',', '.');
            
            Notification::make()
                ->title('Gagal Mengubah Delivery Order!')
                ->body("Qty baru ({$newQtyFormatted}) melebihi qty yang tersedia ({$availableQtyFormatted}). Silakan kurangi qty.")
                ->danger()
                ->duration(7000)
                ->send();
            $this->halt();
        }
        
        // Validasi kapasitas alpal (rob + qtyDiff tidak boleh melebihi kapasitas)
        if ($sp3m->alpal) {
            $alpal = $sp3m->alpal;
            $newRob = $alpal->rob + $qtyDiff;
            
            if ($newRob > $alpal->kapasitas) {
                $newQtyFormatted = number_format($newQty, 0, ',', '.');
                $robFormatted = number_format($alpal->rob, 0, ',', '.');
                $kapasitasFormatted = number_format($alpal->kapasitas, 0, ',', '.');
                $sisaKapasitas = $alpal->kapasitas - $alpal->rob;
                $sisaKapasitasFormatted = number_format($sisaKapasitas, 0, ',', '.');
                
                Notification::make()
                    ->title('Gagal Mengubah Delivery Order!')
                    ->body("Qty baru ({$newQtyFormatted}) melebihi sisa kapasitas alpal. ROB saat ini: {$robFormatted}, Kapasitas: {$kapasitasFormatted}, Sisa kapasitas: {$sisaKapasitasFormatted}.")
                    ->danger()
                    ->duration(7000)
                    ->send();
                $this->halt();
            }
            
            if ($newRob < 0) {
                Notification::make()
                    ->title('Gagal Mengubah Delivery Order!')
                    ->body("ROB tidak boleh negatif.")
                    ->danger()
                    ->duration(5000)
                    ->send();
                $this->halt();
            }
            
            // Update rob di alpal
            $alpal->rob = $newRob;
            $alpal->save();
        }
        
        // Update sisa_qty di SP3M
        $sp3m->sisa_qty = $sp3m->sisa_qty - $qtyDiff;
        $sp3m->save();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getSavedNotification(): ?Notification
    {
        // Send notification immediately
        Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Data delivery order berhasil diperbarui.')
            ->send();
        
        // Return null to prevent Filament from sending it again
        return null;
    }
    
    public function getTitle(): string
    {
        return 'Ubah Delivery Order';
    }
}
