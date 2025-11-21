<?php

namespace App\Filament\Pages\Laporan;

use App\Exports\RekapDoExport;
use App\Models\KantorSar;
use App\Models\DeliveryOrder;
use App\Enums\LevelUser;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RekapDo extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Rekap DO';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.laporan.rekap-do';
    
    /**
     * Check if the current user can access this page
     * Only ADMIN and KANPUS can access Laporan
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        $levelValue = $user->level instanceof LevelUser ? $user->level->value : $user->level;
        
        // Only ADMIN and KANPUS can access Laporan
        return in_array($levelValue, [
            LevelUser::ADMIN->value,
            LevelUser::KANPUS->value,
        ]);
    }

    public ?string $kantor_sar_id = null;
    public ?string $tahun = null;
    public ?array $data = [];

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Select::make('kantor_sar_id')
                            ->label('Kantor SAR')
                            ->options($this->getKantorSarOptions())
                            ->searchable($this->isAdmin())
                            ->disabled(!$this->isAdmin())
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->kantor_sar_id = $state;
                            }),
                        Select::make('tahun')
                            ->label('Tahun')
                            ->options($this->getTahunOptions())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->tahun = $state;
                            }),
                    ])
                    ->statePath('data')
            ),
        ];
    }

    public function mount(): void
    {
        $user = Auth::user();
        
        // For non-admin users, automatically set their kantor_sar_id
        if ($user && $user->level->value !== LevelUser::ADMIN->value && $user->kantor_sar_id) {
            $this->kantor_sar_id = (string) $user->kantor_sar_id;
        }
        
        // Set default year to current year
        $this->tahun = (string) date('Y');
        
        // Initialize form data
        $this->data = [
            'kantor_sar_id' => $this->kantor_sar_id,
            'tahun' => $this->tahun,
        ];
        
        $this->form->fill($this->data);
    }
    
    protected function isAdmin(): bool
    {
        $user = Auth::user();
        return $user && $user->level->value === LevelUser::ADMIN->value;
    }
    
    public function form(Form $form): Form
    {
        return $form;
    }
    
    public function updated($property): void
    {
        if ($property === 'data.kantor_sar_id') {
            $this->kantor_sar_id = $this->data['kantor_sar_id'] ?? null;
        }
        
        if ($property === 'data.tahun') {
            $this->tahun = $this->data['tahun'] ?? null;
        }
    }
    
    protected function getKantorSarOptions(): array
    {
        $user = Auth::user();
        
        // If user is admin, show all Kantor SAR
        if ($user && $user->level->value === LevelUser::ADMIN->value) {
            return KantorSar::pluck('kantor_sar', 'kantor_sar_id')->toArray();
        }
        
        // For non-admin users, only show their assigned Kantor SAR
        if ($user && $user->kantor_sar_id) {
            return KantorSar::where('kantor_sar_id', $user->kantor_sar_id)
                ->pluck('kantor_sar', 'kantor_sar_id')
                ->toArray();
        }
        
        // If no user or no kantor_sar_id assigned, return empty array
        return [];
    }

    protected function getTahunOptions(): array
    {
        // Get available years from delivery orders
        $years = DeliveryOrder::selectRaw('YEAR(tanggal_do) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->mapWithKeys(function ($year) {
                return [$year => $year];
            })
            ->toArray();

        // Add current year if not present
        $currentYear = date('Y');
        if (!isset($years[$currentYear])) {
            $years[$currentYear] = $currentYear;
        }

        return $years;
    }

    public function getFilteredQuery(): Builder
    {
        $user = Auth::user();
        $query = DeliveryOrder::query()
            ->with(['sp3m.kantorSar', 'sp3m.alpal', 'sp3m.bekal']);

        // Apply user-level filtering first
        if ($user && $user->level->value !== LevelUser::ADMIN->value && $user->kantor_sar_id) {
            // Non-admin users can only see data from their assigned Kantor SAR
            $query->whereHas('sp3m', function ($q) use ($user) {
                $q->where('kantor_sar_id', $user->kantor_sar_id);
            });
        } elseif ($this->kantor_sar_id) {
            // Admin users can filter by selected Kantor SAR
            $query->whereHas('sp3m', function ($q) {
                $q->where('kantor_sar_id', $this->kantor_sar_id);
            });
        }

        if ($this->tahun) {
            $query->whereYear('tanggal_do', $this->tahun);
        }

        return $query;
    }

    public function getRekapData(): array
    {
        // Use form data if available, otherwise use property values
        $kantorSarId = $this->data['kantor_sar_id'] ?? $this->kantor_sar_id;
        $tahun = $this->data['tahun'] ?? $this->tahun;
        
        if (!$kantorSarId || !$tahun) {
            return [
                'data' => [],
                'total_bbm' => 0,
                'total_pembayaran' => 0,
            ];
        }
        
        $query = $this->getFilteredQueryForData($kantorSarId, $tahun);
        
        $data = $query->selectRaw('
                MONTH(tanggal_do) as month,
                SUM(qty) as total_bbm,
                SUM(jumlah_harga) as total_pembayaran
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthNames = [
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
            5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
        ];

        $result = [];
        $totalBbm = 0;
        $totalPembayaran = 0;

        foreach ($data as $item) {
            $result[] = [
                'month' => $monthNames[$item->month],
                'total_bbm' => $item->total_bbm,
                'total_pembayaran' => $item->total_pembayaran,
            ];
            $totalBbm += $item->total_bbm;
            $totalPembayaran += $item->total_pembayaran;
        }

        return [
            'data' => $result,
            'total_bbm' => $totalBbm,
            'total_pembayaran' => $totalPembayaran,
        ];
    }
    
    protected function getFilteredQueryForData($kantorSarId, $tahun): Builder
    {
        $user = Auth::user();
        $query = DeliveryOrder::query()
            ->with(['sp3m.kantorSar', 'sp3m.alpal', 'sp3m.bekal']);

        // Apply user-level filtering first
        if ($user && $user->level->value !== LevelUser::ADMIN->value && $user->kantor_sar_id) {
            // Non-admin users can only see data from their assigned Kantor SAR
            $query->whereHas('sp3m', function ($q) use ($user) {
                $q->where('kantor_sar_id', $user->kantor_sar_id);
            });
        } elseif ($kantorSarId) {
            // Admin users can filter by selected Kantor SAR
            $query->whereHas('sp3m', function ($q) use ($kantorSarId) {
                $q->where('kantor_sar_id', $kantorSarId);
            });
        }

        if ($tahun) {
            $query->whereYear('tanggal_do', $tahun);
        }

        return $query;
    }

    public function exportToExcel(): BinaryFileResponse
    {
        $user = Auth::user();
        $kantorSarId = $this->data['kantor_sar_id'] ?? $this->kantor_sar_id;
        $tahun = $this->data['tahun'] ?? $this->tahun;
        
        // For non-admin users, force their assigned kantor_sar_id
        if ($user && $user->level->value !== LevelUser::ADMIN->value && $user->kantor_sar_id) {
            $kantorSarId = $user->kantor_sar_id;
        }
        
        $export = new RekapDoExport(
            $kantorSarId,
            $tahun
        );

        $filename = 'rekap-do-' . ($tahun ?? date('Y')) . '.xlsx';
        return Excel::download($export, $filename);
    }

    protected function getFormActions(): array
    {
        $kantorSarId = $this->data['kantor_sar_id'] ?? $this->kantor_sar_id;
        $tahun = $this->data['tahun'] ?? $this->tahun;
        $isDisabled = !($kantorSarId && $tahun);

        return [
            \Filament\Pages\Actions\Action::make('export')
                ->label('Export to Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->action('exportToExcel')
                ->disabled($isDisabled)
                ->color('success'),
        ];
    }

    public function hasFullWidthFormActions(): bool
    {
        return false;
    }
}