<?php

namespace App\Filament\Pages\Laporan;

use App\Exports\DaftarSp3mExport;
use App\Models\KantorSar;
use App\Enums\RoleEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DaftarSp3m extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Daftar SP3M';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.laporan.daftar-sp3m';

    public ?string $kantor_sar_id = null;
    public ?string $tanggal_start = null;
    public ?string $tanggal_end = null;

    public function mount(): void
    {
        $user = Auth::user();
        
        // For non-admin users, automatically set their kantor_sar_id
        if ($user && $user->level !== RoleEnum::Admin->value && $user->kantor_sar_id) {
            $this->kantor_sar_id = (string) $user->kantor_sar_id;
        }
    }
    
    public function form(Form $form): Form
    {
        $user = Auth::user();
        $isAdmin = $user && $user->level === RoleEnum::Admin->value;
        
        return $form
            ->schema([
                Select::make('kantor_sar_id')
                    ->label('Kantor SAR')
                    ->options($this->getKantorSarOptions())
                    ->searchable($isAdmin) // Only searchable for admin
                    ->disabled(!$isAdmin) // Disable for non-admin users
                    ->preload(),
                DatePicker::make('tanggal_start')
                    ->label('Tanggal Mulai'),
                DatePicker::make('tanggal_end')
                    ->label('Tanggal Selesai'),
            ]);
    }
    
    protected function getKantorSarOptions(): array
    {
        $user = Auth::user();
        
        // If user is admin, show all Kantor SAR
        if ($user && $user->level === RoleEnum::Admin->value) {
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

    public function getFilteredQuery(): Builder
    {
        $user = Auth::user();
        $query = \App\Models\Sp3m::query();

        // Apply user-level filtering first
        if ($user && $user->level !== RoleEnum::Admin->value && $user->kantor_sar_id) {
            // Non-admin users can only see data from their assigned Kantor SAR
            $query->where('kantor_sar_id', $user->kantor_sar_id);
        } elseif ($this->kantor_sar_id) {
            // Admin users can filter by selected Kantor SAR
            $query->where('kantor_sar_id', $this->kantor_sar_id);
        }

        if ($this->tanggal_start) {
            $query->whereDate('created_at', '>=', $this->tanggal_start);
        }

        if ($this->tanggal_end) {
            $query->whereDate('created_at', '<=', $this->tanggal_end);
        }

        return $query;
    }

    public function exportToExcel(): BinaryFileResponse
    {
        $user = Auth::user();
        $kantorSarId = $this->kantor_sar_id;
        
        // For non-admin users, force their assigned kantor_sar_id
        if ($user && $user->level !== RoleEnum::Admin->value && $user->kantor_sar_id) {
            $kantorSarId = $user->kantor_sar_id;
        }
        
        $export = new DaftarSp3mExport(
            $kantorSarId,
            $this->tanggal_start,
            $this->tanggal_end
        );

        return Excel::download($export, 'daftar-sp3m.xlsx');
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Pages\Actions\Action::make('export')
                ->label('Export to Excel')
                ->action('exportToExcel')
                ->color('success'),
        ];
    }

    public function hasFullWidthFormActions(): bool
    {
        return false;
    }
}