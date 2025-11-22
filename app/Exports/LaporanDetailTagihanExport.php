<?php

namespace App\Exports;

use App\Models\ViewLaporanDetailTagihan;
use App\Models\KantorSar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class LaporanDetailTagihanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithColumnFormatting
{
    protected $tanggalAwal;
    protected $tanggalAkhir;
    protected $kantorSarId;
    protected $rowNumber = 0;

    public function __construct($tanggalAwal, $tanggalAkhir, $kantorSarId)
    {
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->kantorSarId = $kantorSarId;
    }

    public function collection()
    {
        $query = ViewLaporanDetailTagihan::query()
            ->whereDate('tanggal_isi', '>=', $this->tanggalAwal)
            ->whereDate('tanggal_isi', '<=', $this->tanggalAkhir);

        if ($this->kantorSarId && $this->kantorSarId !== 'semua') {
            $kantorSar = KantorSar::find($this->kantorSarId);
            if ($kantorSar) {
                $query->where('kantor_sar', $kantorSar->kantor_sar);
            }
        }

        return $query->orderBy('tanggal_isi', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Isi',
            'Nomor SP3M',
            'Nomor DO',
            'Qty (Liter)',
            'Harga per Liter (Rp)',
            'Kantor SAR',
            'Alut',
            'Jenis Bahan Bakar',
            'Jumlah Harga',
            'PPN (11%)',
            'PPKB',
            'Pembulatan',
            'Total',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        
        return [
            $this->rowNumber,
            \Carbon\Carbon::parse($row->tanggal_isi)->format('d-m-Y'),
            $row->nomor_sp3m,
            $row->nomor_do,
            $row->qty,
            $row->harga_per_liter,
            $row->kantor_sar,
            $row->alpal,
            'Dexlite', // Placeholder untuk jenis bahan bakar
            $row->jumlah_harga,
            $row->ppn_11,
            $row->ppkb,
            $row->jumlah_pembulatan,
            $row->total_setelah_pembulatan,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Detail Tagihan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 15,  // Tanggal Isi
            'C' => 20,  // Nomor SP3M
            'D' => 20,  // Nomor DO
            'E' => 15,  // Qty
            'F' => 20,  // Harga per Liter
            'G' => 25,  // Kantor SAR
            'H' => 20,  // Alut
            'I' => 20,  // Jenis Bahan Bakar
            'J' => 20,  // Jumlah Harga
            'K' => 20,  // PPN
            'L' => 20,  // PPKB
            'M' => 15,  // Pembulatan
            'N' => 20,  // Total
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'N' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
