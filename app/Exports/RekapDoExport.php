<?php

namespace App\Exports;

use App\Models\KantorSar;
use App\Models\DeliveryOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RekapDoExport implements FromArray, WithStyles, WithTitle, WithColumnWidths
{
    protected $kantor_sar_id;
    protected $tahun;

    public function __construct($kantor_sar_id = null, $tahun = null)
    {
        $this->kantor_sar_id = $kantor_sar_id;
        $this->tahun = $tahun ?? date('Y');
    }

    /**
     * @return array
     */
    public function array(): array
    {
        $data = [];
        
        // Get Kantor SAR name
        $kantorSarName = 'Semua Kantor SAR';
        if ($this->kantor_sar_id) {
            $kantorSar = KantorSar::find($this->kantor_sar_id);
            $kantorSarName = $kantorSar ? $kantorSar->kantor_sar : 'Kantor SAR Tidak Ditemukan';
        }
        
        // Title section
        $data[] = ['REKAP TAGIHAN PENGGUNAAN BBM', '', '', ''];
        $data[] = ['Kantor SAR: ' . $kantorSarName, '', '', ''];
        $data[] = ['Tahun: ' . $this->tahun, '', '', ''];
        $data[] = ['', '', '', ''];
        
        // Headers
        $data[] = [
            'NO',
            'PERIODE',
            'JMLAH BBM (Liter)',
            'JUMLAH PEMBAYARAN (Rp)'
        ];
        
        // Get monthly data
        $monthlyData = $this->getMonthlyData();
        
        $monthNames = [
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
            5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
        ];
        
        $totalBbm = 0;
        $totalPembayaran = 0;
        
        // Create array for all 12 months
        $allMonths = [];
        for ($month = 1; $month <= 12; $month++) {
            $found = false;
            foreach ($monthlyData as $item) {
                if ($item->month == $month) {
                    $allMonths[] = [
                        'no' => $month,
                        'month' => $monthNames[$month],
                        'bbm' => number_format($item->total_bbm, 0, ',', '.'),
                        'pembayaran' => number_format($item->total_pembayaran, 2, ',', '.')
                    ];
                    $totalBbm += $item->total_bbm;
                    $totalPembayaran += $item->total_pembayaran;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $allMonths[] = [
                    'no' => $month,
                    'month' => $monthNames[$month],
                    'bbm' => '-',
                    'pembayaran' => '-'
                ];
            }
        }
        
        // Add data rows for all months
        foreach ($allMonths as $item) {
            $data[] = [
                $item['no'],
                $item['month'],
                $item['bbm'],
                $item['pembayaran']
            ];
        }
        
        // Add total row
        $data[] = ['', 'JUMLAH', number_format($totalBbm, 0, ',', '.'), number_format($totalPembayaran, 2, ',', '.')];
        
        return $data;
    }
    
    protected function getMonthlyData()
    {
        $query = DeliveryOrder::query()
            ->with(['sp3m.kantorSar']);

        // Filter by Kantor SAR if specified
        if ($this->kantor_sar_id) {
            $kantorSarId = $this->kantor_sar_id;
            $query->whereHas('sp3m', function ($q) use ($kantorSarId) {
                $q->where('kantor_sar_id', $kantorSarId);
            });
        }

        // Filter by year
        $query->whereYear('tanggal_do', $this->tahun);
        
        return $query
            ->leftJoin('ms_harga_bekal', function($join) {
                $join->on('tx_do.kota_id', '=', 'ms_harga_bekal.kota_id')
                     ->on('tx_do.bekal_id', '=', 'ms_harga_bekal.bekal_id');
            })
            ->selectRaw('
                MONTH(tx_do.tanggal_do) as month,
                SUM(tx_do.qty) as total_bbm,
                SUM(tx_do.qty * COALESCE(ms_harga_bekal.harga, 0)) as total_pembayaran
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();
        
        // Title styling (row 1)
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Kantor SAR and Year info styling (rows 2-3)
        $sheet->getStyle('A2:A3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
        ]);
        
        // Header row styling (row 5)
        $sheet->getStyle('A5:D5')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E2E8F0',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
        
        // Data rows styling
        if ($lastRow > 5) {
            // All data cells border
            $sheet->getStyle('A6:D' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
            
            // NO column (A) - center alignment
            $sheet->getStyle('A6:A' . ($lastRow - 1))->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            
            // JMLAH BBM column (C) - right alignment
            $sheet->getStyle('C6:C' . $lastRow)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ]);
            
            // JUMLAH PEMBAYARAN column (D) - right alignment
            $sheet->getStyle('D6:D' . $lastRow)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ]);
            
            // Total row styling (last row)
            $sheet->getStyle('A' . $lastRow . ':D' . $lastRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'F1F5F9',
                    ],
                ],
            ]);
        }
        
        // Merge title cell
        $sheet->mergeCells('A1:D1');
        
        return [];
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // NO
            'B' => 15,  // PERIODE
            'C' => 20,  // JMLAH BBM (Liter)
            'D' => 25,  // JUMLAH PEMBAYARAN (Rp)
        ];
    }

    public function title(): string
    {
        return 'Rekap DO ' . $this->tahun;
    }
}