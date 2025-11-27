<?php

namespace App\Filament\Widgets;

use App\Models\GolonganBbm;
use App\Models\Pagu;
use App\Models\Sp3m;
use App\Models\DeliveryOrder;
use App\Models\Pemakaian;
use App\Models\Bekal;
use Filament\Widgets\Widget;

class DashboardStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-stats-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    public $selectedYear;
    public $golonganBbmData = [];

    public function mount(): void
    {
        $this->selectedYear = Pagu::select('tahun_anggaran')
            ->orderBy('tahun_anggaran', 'desc')
            ->value('tahun_anggaran') ?? date('Y');
        
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
        // Get total pagu
        $totalPagu = Pagu::where('golongan_bbm_id', $golonganBbmId)
            ->where('tahun_anggaran', $this->selectedYear)
            ->sum('nilai_pagu');

        // Get bekal IDs for this golongan
        $bekalIds = Bekal::where('golongan_bbm_id', $golonganBbmId)->pluck('bekal_id');

        // Get TW data from SP3M
        $tw1 = Sp3m::whereIn('bekal_id', $bekalIds)
            ->where('tahun_anggaran', $this->selectedYear)
            ->where('tw', 1)
            ->sum('jumlah_harga');

        $tw2 = Sp3m::whereIn('bekal_id', $bekalIds)
            ->where('tahun_anggaran', $this->selectedYear)
            ->where('tw', 2)
            ->sum('jumlah_harga');

        $tw3 = Sp3m::whereIn('bekal_id', $bekalIds)
            ->where('tahun_anggaran', $this->selectedYear)
            ->where('tw', 3)
            ->sum('jumlah_harga');

        $tw4 = Sp3m::whereIn('bekal_id', $bekalIds)
            ->where('tahun_anggaran', $this->selectedYear)
            ->where('tw', 4)
            ->sum('jumlah_harga');

        return [
            'total' => $totalPagu ?? 0,
            'tw1' => $tw1 ?? 0,
            'tw2' => $tw2 ?? 0,
            'tw3' => $tw3 ?? 0,
            'tw4' => $tw4 ?? 0,
        ];
    }

    protected function getSp3mData($golonganBbmId): array
    {
        $bekalIds = Bekal::where('golongan_bbm_id', $golonganBbmId)->pluck('bekal_id');

        if ($bekalIds->isEmpty()) {
            return ['bekal' => '-', 'qty' => 0, 'jumlah_harga' => 0, 'sisa_qty' => 0];
        }

        $sp3mData = Sp3m::whereIn('bekal_id', $bekalIds)
            ->where('tahun_anggaran', $this->selectedYear)
            ->selectRaw('SUM(qty) as total_qty, SUM(jumlah_harga) as total_harga, SUM(sisa_qty) as total_sisa_qty')
            ->first();

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
            return ['bekal' => '-', 'qty' => 0, 'jumlah_harga' => 0, 'sisa_sp3m' => 0];
        }

        $pengambilanData = DeliveryOrder::whereHas('sp3m', function ($query) use ($bekalIds) {
                $query->whereIn('bekal_id', $bekalIds);
            })
            ->where('tahun_anggaran', $this->selectedYear)
            ->leftJoin('ms_harga_bekal', function($join) {
                $join->on('tx_do.kota_id', '=', 'ms_harga_bekal.kota_id')
                     ->on('tx_do.bekal_id', '=', 'ms_harga_bekal.bekal_id');
            })
            ->selectRaw('SUM(tx_do.qty) as total_qty, SUM(tx_do.qty * COALESCE(ms_harga_bekal.harga, 0)) as total_harga')
            ->first();

        $bekalName = Bekal::whereIn('bekal_id', $bekalIds)->first()->bekal ?? '-';
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
            return ['bekal' => '-', 'qty' => 0, 'pengisian' => 0];
        }

        // Get pemakaian data
        $pemakaianData = Pemakaian::whereIn('bekal_id', $bekalIds)
            ->whereYear('tanggal_pakai', $this->selectedYear)
            ->selectRaw('SUM(qty) as total_qty')
            ->first();

        // Get pengisian data from Delivery Order
        $pengisianData = DeliveryOrder::whereHas('sp3m', function ($query) use ($bekalIds) {
                $query->whereIn('bekal_id', $bekalIds);
            })
            ->where('tahun_anggaran', $this->selectedYear)
            ->sum('qty');

        $bekalName = Bekal::whereIn('bekal_id', $bekalIds)->first()->bekal ?? '-';

        return [
            'bekal' => $bekalName,
            'qty' => $pemakaianData->total_qty ?? 0,
            'pengisian' => $pengisianData ?? 0,
        ];
    }

    public function getYearOptions(): array
    {
        $years = Pagu::select('tahun_anggaran')
            ->distinct()
            ->orderBy('tahun_anggaran', 'desc')
            ->pluck('tahun_anggaran', 'tahun_anggaran')
            ->toArray();
        
        if (empty($years)) {
            $currentYear = date('Y');
            return [$currentYear => $currentYear];
        }
        
        return $years;
    }
}
