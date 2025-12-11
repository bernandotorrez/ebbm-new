<?php

namespace App\Exports;

use App\Models\KantorSar;
use App\Models\Sp3m;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DaftarSp3mExport implements FromArray, WithStyles, WithTitle, WithColumnWidths
{
    protected $kantor_sar_id;
    protected $tanggal_start;
    protected $tanggal_end;

    public function __construct($kantor_sar_id = null, $tanggal_start = null, $tanggal_end = null)
    {
        $this->kantor_sar_id = $kantor_sar_id;
        $this->tanggal_start = $tanggal_start;
        $this->tanggal_end = $tanggal_end;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        $data = [];
        
        // Add information header
        $kantorSarName = 'Semua Kantor SAR';
        if ($this->kantor_sar_id) {
            $kantorSar = KantorSar::find($this->kantor_sar_id);
            $kantorSarName = $kantorSar ? $kantorSar->kantor_sar : 'Kantor SAR Tidak Ditemukan';
        }
        
        $tanggalStart = $this->tanggal_start ? date('d/m/Y', strtotime($this->tanggal_start)) : 'Tidak Ditentukan';
        $tanggalEnd = $this->tanggal_end ? date('d/m/Y', strtotime($this->tanggal_end)) : 'Tidak Ditentukan';
        
        // Information rows
        $data[] = ['LAPORAN DAFTAR SP3M', '', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '', ''];
        $data[] = ['Kantor SAR:', $kantorSarName, '', '', '', '', '', '', ''];
        $data[] = ['Tanggal Mulai:', $tanggalStart, '', '', '', '', '', '', ''];
        $data[] = ['Tanggal Selesai:', $tanggalEnd, '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '', ''];
        
        // Headers
        $data[] = [
            'No',
            'Nomor SP3M',
            'Kantor SAR',
            'Alut',
            'Jenis Bahan Bakar',
            'Qty',
            'Harga Satuan',
            'Jumlah Harga',
            'Tanggal Dibuat',
        ];
        
        // Get data
        $query = Sp3m::with(['kantorSar', 'alpal', 'bekal']);

        if ($this->kantor_sar_id) {
            $query->where('kantor_sar_id', $this->kantor_sar_id);
        }

        if ($this->tanggal_start) {
            $query->whereDate('created_at', '>=', $this->tanggal_start);
        }

        if ($this->tanggal_end) {
            $query->whereDate('created_at', '<=', $this->tanggal_end);
        }

        $sp3ms = $query->get();
        
        // Add data rows with row number
        $rowNumber = 1;
        foreach ($sp3ms as $sp3m) {
            $data[] = [
                $rowNumber++,
                $sp3m->nomor_sp3m,
                $sp3m->kantorSar->kantor_sar ?? '',
                $sp3m->alpal->alpal ?? '',
                $sp3m->bekal->bekal ?? '',
                $sp3m->qty,
                'Rp ' . number_format($sp3m->harga_satuan, 0, ',', '.'),
                'Rp ' . number_format($sp3m->jumlah_harga, 0, ',', '.'),
                $sp3m->created_at->format('d/m/Y H:i:s'),
            ];
        }
        
        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();
        
        // Title styling (row 1)
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Information section styling (rows 3-5)
        $sheet->getStyle('A3:A5')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
        ]);
        
        // Header row styling (row 7)
        $sheet->getStyle('A7:I7')->applyFromArray([
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
        if ($lastRow > 7) {
            // All data cells border
            $sheet->getStyle('A8:I' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
            
            // No column (A) - center alignment
            $sheet->getStyle('A8:A' . $lastRow)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            
            // Qty column (F) - center alignment
            $sheet->getStyle('F8:F' . $lastRow)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            
            // Harga Satuan column (G) - right alignment
            $sheet->getStyle('G8:G' . $lastRow)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ]);
            
            // Jumlah Harga column (H) - right alignment
            $sheet->getStyle('H8:H' . $lastRow)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ]);
        }
        
        // Merge title cell
        $sheet->mergeCells('A1:I1');
        
        return [];
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 20,  // Nomor SP3M
            'C' => 25,  // Kantor SAR
            'D' => 20,  // Alut
            'E' => 20,  // Jenis Bahan Bakar
            'F' => 10,  // Qty
            'G' => 18,  // Harga Satuan
            'H' => 18,  // Jumlah Harga
            'I' => 20,  // Tanggal Dibuat
        ];
    }

    public function title(): string
    {
        return 'Daftar SP3M';
    }
}