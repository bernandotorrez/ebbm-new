<?php

namespace App\Filament\Pages;

use App\Models\GolonganBbm;
use App\Models\Pagu;
use App\Models\Sp3m;
use App\Models\DeliveryOrder;
use App\Models\Pemakaian;
use App\Models\Bekal;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;
    
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardStatsWidget::class,
        ];
    }

    public $selectedYear;
    public $golonganBbmData = [];

    public function mount(): void
    {
        // Get the latest year from tx_pagu, or use current year as fallback
        $latestYear = Pagu::select('tahun_anggaran')
            ->orderBy('tahun_anggaran', 'desc')
            ->value('tahun_anggaran');
        
        $this->selectedYear = $latestYear ?? date('Y');
        $this->loadDashboardData();
    }

    public function updatedSelectedYear(): void
    {
        $this->loadDashboardData();
    }

    protected function loadDashboardData(): void
    {
        $golonganBbms = GolonganBbm::all();
        
        foreach ($golonganBbms as $golongan) {
            $this->golonganBbmData[$golongan->golongan_bbm_id] = [
                'golongan' => $golongan->golongan,
                'pagu' => $this->getPaguData($golongan->golongan_bbm_id),
                'sp3m' => $this->getSp3mData($golongan->golongan_bbm_id),
                'pengambilan' => $this->getPengambilanData($golongan->golongan_bbm_id),
                'pemakaian' => $this->getPemakaianData($golongan->golongan_bbm_id),
            ];
        }
    }

    protected function getPaguData($golonganBbmId): array
    {
        $paguData = Pagu::where('golongan_bbm_id', $golonganBbmId)
            ->where('tahun_anggaran', $this->selectedYear)
            ->selectRaw('
                SUM(nilai_pagu) as total_pagu,
                SUM(CASE WHEN dasar LIKE "%TW 1%" OR dasar LIKE "%TW1%" THEN nilai_pagu ELSE 0 END) as tw1,
                SUM(CASE WHEN dasar LIKE "%TW 2%" OR dasar LIKE "%TW2%" THEN nilai_pagu ELSE 0 END) as tw2,
                SUM(CASE WHEN dasar LIKE "%TW 3%" OR dasar LIKE "%TW3%" THEN nilai_pagu ELSE 0 END) as tw3,
                SUM(CASE WHEN dasar LIKE "%TW 4%" OR dasar LIKE "%TW4%" THEN nilai_pagu ELSE 0 END) as tw4
            ')
            ->first();

        return [
            'total' => $paguData->total_pagu ?? 0,
            'tw1' => $paguData->tw1 ?? 0,
            'tw2' => $paguData->tw2 ?? 0,
            'tw3' => $paguData->tw3 ?? 0,
            'tw4' => $paguData->tw4 ?? 0,
            'sisa' => 0, // Will be calculated after getting SP3M data
        ];
    }

    protected function getSp3mData($golonganBbmId): array
    {
        $bekalIds = Bekal::where('golongan_bbm_id', $golonganBbmId)->pluck('bekal_id');

        if ($bekalIds->isEmpty()) {
            return [
                'bekal' => '-',
                'qty' => 0,
                'jumlah_harga' => 0,
                'sisa_qty' => 0,
            ];
        }

        $query = Sp3m::whereIn('bekal_id', $bekalIds)
            ->where('tahun_anggaran', $this->selectedYear);

        // Filter by kantor_sar_id for Kansar and ABK
        if (in_array(auth()->user()->level->value, ['kansar', 'abk'])) {
            $query->where('kantor_sar_id', auth()->user()->kantor_sar_id);
        }

        $sp3mData = $query->selectRaw('
                SUM(qty) as total_qty,
                SUM(jumlah_harga) as total_harga,
                SUM(sisa_qty) as total_sisa_qty
            ')
            ->first();

        // Get bekal name (first bekal in the group)
        $bekalName = Bekal::whereIn('bekal_id', $bekalIds)->first()->bekal ?? '-';

        return [
            'bekal' => $bekalName,
            'qty' => $sp3mData->total_qty ?? 0,
            'jumlah_harga' => $sp3mData->total_harga ?? 0,
            'sisa_qty' => $sp3mData->total_sisa_qty ?? 0,
        ];
    }

    protected function getPengambilanData($golonganBbmId): array
    {
        $bekalIds = Bekal::where('golongan_bbm_id', $golonganBbmId)->pluck('bekal_id');

        if ($bekalIds->isEmpty()) {
            return [
                'bekal' => '-',
                'qty' => 0,
                'jumlah_harga' => 0,
                'sisa_sp3m' => 0,
            ];
        }

        $query = DeliveryOrder::whereHas('sp3m', function ($query) use ($bekalIds) {
                $query->whereIn('bekal_id', $bekalIds);
                
                // Filter by kantor_sar_id for Kansar and ABK
                if (in_array(auth()->user()->level->value, ['kansar', 'abk'])) {
                    $query->where('kantor_sar_id', auth()->user()->kantor_sar_id);
                }
            })
            ->where('tahun_anggaran', $this->selectedYear);

        $pengambilanData = $query
            ->leftJoin('ms_harga_bekal', function($join) {
                $join->on('tx_do.kota_id', '=', 'ms_harga_bekal.kota_id')
                     ->on('tx_do.bekal_id', '=', 'ms_harga_bekal.bekal_id');
            })
            ->selectRaw('
                SUM(tx_do.qty) as total_qty,
                SUM(tx_do.qty * COALESCE(ms_harga_bekal.harga, 0)) as total_harga
            ')
            ->first();

        // Get bekal name
        $bekalName = Bekal::whereIn('bekal_id', $bekalIds)->first()->bekal ?? '-';

        // Calculate sisa SP3M
        $sisaSp3m = $this->getSp3mData($golonganBbmId)['sisa_qty'];

        return [
            'bekal' => $bekalName,
            'qty' => $pengambilanData->total_qty ?? 0,
            'jumlah_harga' => $pengambilanData->total_harga ?? 0,
            'sisa_sp3m' => $sisaSp3m,
        ];
    }

    protected function getPemakaianData($golonganBbmId): array
    {
        $bekalIds = Bekal::where('golongan_bbm_id', $golonganBbmId)->pluck('bekal_id');

        if ($bekalIds->isEmpty()) {
            return [
                'bekal' => '-',
                'qty' => 0,
                'pengisian' => 0,
            ];
        }

        $queryPemakaian = Pemakaian::whereIn('bekal_id', $bekalIds)
            ->whereYear('tanggal_pakai', $this->selectedYear);

        // Filter by kantor_sar_id for Kansar and ABK
        if (in_array(auth()->user()->level->value, ['kansar', 'abk'])) {
            $queryPemakaian->where('kantor_sar_id', auth()->user()->kantor_sar_id);
        }

        $pemakaianData = $queryPemakaian->selectRaw('
                SUM(qty) as total_qty
            ')
            ->first();

        // Calculate pengisian from DeliveryOrder
        $queryPengisian = DeliveryOrder::whereHas('sp3m', function ($query) use ($bekalIds) {
                $query->whereIn('bekal_id', $bekalIds);
                
                // Filter by kantor_sar_id for Kansar and ABK
                if (in_array(auth()->user()->level->value, ['kansar', 'abk'])) {
                    $query->where('kantor_sar_id', auth()->user()->kantor_sar_id);
                }
            })
            ->where('tahun_anggaran', $this->selectedYear);

        $pengisianData = $queryPengisian->sum('qty');

        // Get bekal name
        $bekalName = Bekal::whereIn('bekal_id', $bekalIds)->first()->bekal ?? '-';

        return [
            'bekal' => $bekalName,
            'qty' => $pemakaianData->total_qty ?? 0,
            'pengisian' => $pengisianData ?? 0,
        ];
    }

    public function getYearOptions(): array
    {
        // Get distinct years from tx_pagu table
        $years = Pagu::select('tahun_anggaran')
            ->distinct()
            ->orderBy('tahun_anggaran', 'desc')
            ->pluck('tahun_anggaran', 'tahun_anggaran')
            ->toArray();
        
        // If no years found, return current year as default
        if (empty($years)) {
            $currentYear = date('Y');
            return [$currentYear => $currentYear];
        }
        
        return $years;
    }
}
