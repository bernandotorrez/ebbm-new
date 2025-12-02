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

        // Get TW data from tx_do dengan join ke ms_harga_bekal dan tx_sp3m
        // Hitung: tx_do.qty * ms_harga_bekal.harga, dikelompokkan per TW dari SP3M
        $tw1 = \DB::table('tx_do')
            ->join('tx_sp3m', 'tx_do.sp3m_id', '=', 'tx_sp3m.sp3m_id')
            ->leftJoin('ms_harga_bekal', function($join) {
                $join->on('tx_do.harga_bekal_id', '=', 'ms_harga_bekal.harga_bekal_id');
            })
            ->whereIn('tx_sp3m.bekal_id', $bekalIds)
            ->where('tx_do.tahun_anggaran', $this->selectedYear)
            ->where('tx_sp3m.tw', 1)
            ->whereNull('tx_do.deleted_at')
            ->sum(\DB::raw('tx_do.qty * COALESCE(ms_harga_bekal.harga, 0)'));

        $tw2 = \DB::table('tx_do')
            ->join('tx_sp3m', 'tx_do.sp3m_id', '=', 'tx_sp3m.sp3m_id')
            ->leftJoin('ms_harga_bekal', function($join) {
                $join->on('tx_do.harga_bekal_id', '=', 'ms_harga_bekal.harga_bekal_id');
            })
            ->whereIn('tx_sp3m.bekal_id', $bekalIds)
            ->where('tx_do.tahun_anggaran', $this->selectedYear)
            ->where('tx_sp3m.tw', 2)
            ->whereNull('tx_do.deleted_at')
            ->sum(\DB::raw('tx_do.qty * COALESCE(ms_harga_bekal.harga, 0)'));

        $tw3 = \DB::table('tx_do')
            ->join('tx_sp3m', 'tx_do.sp3m_id', '=', 'tx_sp3m.sp3m_id')
            ->leftJoin('ms_harga_bekal', function($join) {
                $join->on('tx_do.harga_bekal_id', '=', 'ms_harga_bekal.harga_bekal_id');
            })
            ->whereIn('tx_sp3m.bekal_id', $bekalIds)
            ->where('tx_do.tahun_anggaran', $this->selectedYear)
            ->where('tx_sp3m.tw', 3)
            ->whereNull('tx_do.deleted_at')
            ->sum(\DB::raw('tx_do.qty * COALESCE(ms_harga_bekal.harga, 0)'));

        $tw4 = \DB::table('tx_do')
            ->join('tx_sp3m', 'tx_do.sp3m_id', '=', 'tx_sp3m.sp3m_id')
            ->leftJoin('ms_harga_bekal', function($join) {
                $join->on('tx_do.harga_bekal_id', '=', 'ms_harga_bekal.harga_bekal_id');
            })
            ->whereIn('tx_sp3m.bekal_id', $bekalIds)
            ->where('tx_do.tahun_anggaran', $this->selectedYear)
            ->where('tx_sp3m.tw', 4)
            ->whereNull('tx_do.deleted_at')
            ->sum(\DB::raw('tx_do.qty * COALESCE(ms_harga_bekal.harga, 0)'));

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

        // Get user dari session
        $user = auth()->user();
        
        // Build query
        $query = Sp3m::whereIn('bekal_id', $bekalIds)
            ->where('tahun_anggaran', $this->selectedYear);

        // Filter khusus untuk ABK berdasarkan alpal_id
        if ($user && $user->level->value === \App\Enums\LevelUser::ABK->value) {
            // Jika tidak punya alpal_id, return empty data
            if (!$user->alpal_id) {
                return [
                    'bekal' => '-',
                    'qty' => 0,
                    'jumlah_harga' => 0,
                    'sisa_qty' => 0,
                ];
            }
            
            // Filter berdasarkan alpal_id user
            $query->where('alpal_id', $user->alpal_id);
        }
        // Filter untuk Kansar berdasarkan kantor_sar_id
        elseif ($user && $user->level->value === \App\Enums\LevelUser::KANSAR->value) {
            // Jika tidak punya kantor_sar_id, return empty data
            if (!$user->kantor_sar_id) {
                return [
                    'bekal' => '-',
                    'qty' => 0,
                    'jumlah_harga' => 0,
                    'sisa_qty' => 0,
                ];
            }
            
            // Filter berdasarkan kantor_sar_id user
            $query->where('kantor_sar_id', $user->kantor_sar_id);
        }

        $sp3mData = $query->selectRaw('SUM(qty) as total_qty, SUM(sisa_qty) as total_sisa_qty')
            ->first();

        // Hitung jumlah_harga dari tx_do (bukan dari sp3m)
        $doQuery = \DB::table('tx_do')
            ->join('tx_sp3m', 'tx_do.sp3m_id', '=', 'tx_sp3m.sp3m_id')
            ->leftJoin('ms_harga_bekal', function($join) {
                $join->on('tx_do.harga_bekal_id', '=', 'ms_harga_bekal.harga_bekal_id');
            })
            ->whereIn('tx_sp3m.bekal_id', $bekalIds)
            ->where('tx_do.tahun_anggaran', $this->selectedYear)
            ->whereNull('tx_do.deleted_at');

        // Apply same filters as SP3M query
        if ($user && $user->level->value === \App\Enums\LevelUser::ABK->value && $user->alpal_id) {
            $doQuery->where('tx_sp3m.alpal_id', $user->alpal_id);
        } elseif ($user && $user->level->value === \App\Enums\LevelUser::KANSAR->value && $user->kantor_sar_id) {
            $doQuery->where('tx_sp3m.kantor_sar_id', $user->kantor_sar_id);
        }

        $jumlahHarga = $doQuery->sum(\DB::raw('tx_do.qty * COALESCE(ms_harga_bekal.harga, 0)'));

        $bekalName = Bekal::whereIn('bekal_id', $bekalIds)->first()->bekal ?? '-';

        return [
            'bekal' => $bekalName,
            'qty' => $sp3mData->total_qty ?? 0,
            'jumlah_harga' => $jumlahHarga ?? 0,
            'sisa_qty' => $sp3mData->total_sisa_qty ?? 0,
        ];
    }

    protected function getPengambilanData($golonganBbmId): array
    {
        $bekalIds = Bekal::where('golongan_bbm_id', $golonganBbmId)->pluck('bekal_id');

        if ($bekalIds->isEmpty()) {
            return ['bekal' => '-', 'qty' => 0, 'jumlah_harga' => 0, 'sisa_sp3m' => 0];
        }

        $user = auth()->user();
        
        // Return empty jika user ABK tidak punya alpal_id
        if ($user && $user->level->value === \App\Enums\LevelUser::ABK->value && !$user->alpal_id) {
            return [
                'bekal' => '-',
                'qty' => 0,
                'jumlah_harga' => 0,
                'sisa_sp3m' => 0,
            ];
        }
        
        // Return empty jika user Kansar tidak punya kantor_sar_id
        if ($user && $user->level->value === \App\Enums\LevelUser::KANSAR->value && !$user->kantor_sar_id) {
            return [
                'bekal' => '-',
                'qty' => 0,
                'jumlah_harga' => 0,
                'sisa_sp3m' => 0,
            ];
        }
        
        $query = DeliveryOrder::whereHas('sp3m', function ($query) use ($bekalIds, $user) {
                $query->whereIn('bekal_id', $bekalIds);
                
                // Filter khusus untuk ABK berdasarkan alpal_id
                if ($user && $user->level->value === \App\Enums\LevelUser::ABK->value && $user->alpal_id) {
                    $query->where('alpal_id', $user->alpal_id);
                }
                // Filter untuk Kansar berdasarkan kantor_sar_id
                elseif ($user && $user->level->value === \App\Enums\LevelUser::KANSAR->value && $user->kantor_sar_id) {
                    $query->where('kantor_sar_id', $user->kantor_sar_id);
                }
            })
            ->where('tahun_anggaran', $this->selectedYear);

        $pengambilanData = $query
            ->leftJoin('ms_harga_bekal', function($join) {
                $join->on('tx_do.harga_bekal_id', '=', 'ms_harga_bekal.harga_bekal_id');
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

        $user = auth()->user();
        
        // Return empty jika user ABK tidak punya alpal_id
        if ($user && $user->level->value === \App\Enums\LevelUser::ABK->value && !$user->alpal_id) {
            return [
                'bekal' => '-',
                'qty' => 0,
                'pengisian' => 0,
            ];
        }
        
        // Return empty jika user Kansar tidak punya kantor_sar_id
        if ($user && $user->level->value === \App\Enums\LevelUser::KANSAR->value && !$user->kantor_sar_id) {
            return [
                'bekal' => '-',
                'qty' => 0,
                'pengisian' => 0,
            ];
        }
        
        $queryPemakaian = Pemakaian::whereIn('bekal_id', $bekalIds)
            ->whereYear('tanggal_pakai', $this->selectedYear);

        // Filter khusus untuk ABK berdasarkan alpal_id
        if ($user && $user->level->value === \App\Enums\LevelUser::ABK->value && $user->alpal_id) {
            $queryPemakaian->where('alpal_id', $user->alpal_id);
        }
        // Filter untuk Kansar berdasarkan kantor_sar_id
        elseif ($user && $user->level->value === \App\Enums\LevelUser::KANSAR->value && $user->kantor_sar_id) {
            $queryPemakaian->where('kantor_sar_id', $user->kantor_sar_id);
        }

        $pemakaianData = $queryPemakaian->selectRaw('SUM(qty) as total_qty')
            ->first();

        // Calculate pengisian from DeliveryOrder
        $queryPengisian = DeliveryOrder::whereHas('sp3m', function ($query) use ($bekalIds, $user) {
                $query->whereIn('bekal_id', $bekalIds);
                
                // Filter khusus untuk ABK berdasarkan alpal_id
                if ($user && $user->level->value === \App\Enums\LevelUser::ABK->value && $user->alpal_id) {
                    $query->where('alpal_id', $user->alpal_id);
                }
                // Filter untuk Kansar berdasarkan kantor_sar_id
                elseif ($user && $user->level->value === \App\Enums\LevelUser::KANSAR->value && $user->kantor_sar_id) {
                    $query->where('kantor_sar_id', $user->kantor_sar_id);
                }
            })
            ->where('tahun_anggaran', $this->selectedYear);

        $pengisianData = $queryPengisian->sum('qty');

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
