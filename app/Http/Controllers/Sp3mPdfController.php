<?php

namespace App\Http\Controllers;

use App\Models\Sp3m;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class Sp3mPdfController extends Controller
{
    /**
     * Export SP3M to PDF (Preview in browser)
     */
    public function exportPdf($id)
    {
        // Cek autentikasi
        if (!auth()->check()) {
            abort(403, 'Unauthorized access');
        }

        // Ambil data SP3M dengan relasi
        $sp3m = Sp3m::with([
            'alpal.kantorSar.kota',
            'kantorSar',
            'bekal',
            'tbbm'
        ])->findOrFail($id);

        // Ambil data Pagu berdasarkan tahun anggaran
        $pagu = \App\Models\Pagu::where('tahun_anggaran', $sp3m->tahun_anggaran)
            ->orderBy('tanggal', 'desc')
            ->first();

        // Extract bulan dari nomor SP3M (format: 0001/SP3M.026/XII/SAR-2025)
        $bulanRomawi = $this->extractBulanFromNomorSp3m($sp3m->nomor_sp3m);
        $bulanIndonesia = $this->romawiToIndonesia($bulanRomawi);

        // Prepare logo base64 (disabled - use placeholder to avoid memory issues)
        // $logoBase64 = null;

        // Load view PDF
        $pdf = Pdf::loadView('pdf.sp3m', [
            'sp3m' => $sp3m,
            'pagu' => $pagu,
            'bulanIndonesia' => $bulanIndonesia,
            // 'logoBase64' => $logoBase64
        ]);

        // Set paper size dan orientation
        $pdf->setPaper('a4', 'portrait');

        // Generate filename
        $filename = 'SP3M_' . str_replace(['/', ' '], '_', $sp3m->nomor_sp3m) . '.pdf';

        // Return PDF stream (preview in browser)
        return $pdf->stream($filename);
    }
    
    /**
     * Extract bulan romawi dari nomor SP3M
     */
    private function extractBulanFromNomorSp3m($nomorSp3m)
    {
        // Format: 0001/SP3M.026/XII/SAR-2025
        // Extract XII (bulan romawi)
        preg_match('/\/([IVX]+)\//', $nomorSp3m, $matches);
        return $matches[1] ?? 'I';
    }
    
    /**
     * Convert bulan romawi ke nama bulan Indonesia
     */
    private function romawiToIndonesia($romawi)
    {
        $bulan = [
            'I' => 'Januari',
            'II' => 'Februari',
            'III' => 'Maret',
            'IV' => 'April',
            'V' => 'Mei',
            'VI' => 'Juni',
            'VII' => 'Juli',
            'VIII' => 'Agustus',
            'IX' => 'September',
            'X' => 'Oktober',
            'XI' => 'November',
            'XII' => 'Desember'
        ];
        
        return $bulan[$romawi] ?? 'Januari';
    }
    
    /**
     * Get logo as base64 encoded string
     * Convert PNG to JPEG for better DomPDF compatibility
     */
    private function getLogoBase64()
    {
        $logoPath = public_path('logo.png');
        
        if (!file_exists($logoPath)) {
            return null;
        }
        
        try {
            // Load image
            $image = @imagecreatefrompng($logoPath);
            
            if (!$image) {
                return null;
            }
            
            // Create white background for transparency
            $width = imagesx($image);
            $height = imagesy($image);
            $output = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($output, 255, 255, 255);
            imagefill($output, 0, 0, $white);
            
            // Copy image
            imagecopy($output, $image, 0, 0, 0, 0, $width, $height);
            
            // Output to buffer as JPEG (smaller size)
            ob_start();
            imagejpeg($output, null, 85); // 85% quality
            $imageData = ob_get_clean();
            
            // Clean up
            imagedestroy($image);
            imagedestroy($output);
            
            return 'data:image/jpeg;base64,' . base64_encode($imageData);
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Download SP3M PDF
     */
    public function downloadPdf($id)
    {
        // Cek autentikasi
        if (!auth()->check()) {
            abort(403, 'Unauthorized access');
        }

        // Ambil data SP3M dengan relasi
        $sp3m = Sp3m::with([
            'alpal.kantorSar.kota',
            'kantorSar',
            'bekal',
            'tbbm'
        ])->findOrFail($id);

        // Ambil data Pagu berdasarkan tahun anggaran
        $pagu = \App\Models\Pagu::where('tahun_anggaran', $sp3m->tahun_anggaran)
            ->orderBy('tanggal', 'desc')
            ->first();

        // Extract bulan dari nomor SP3M
        $bulanRomawi = $this->extractBulanFromNomorSp3m($sp3m->nomor_sp3m);
        $bulanIndonesia = $this->romawiToIndonesia($bulanRomawi);

        // Prepare logo base64 (disabled - use placeholder to avoid memory issues)
        $logoBase64 = null;

        // Load view PDF
        $pdf = Pdf::loadView('pdf.sp3m', [
            'sp3m' => $sp3m,
            'pagu' => $pagu,
            'bulanIndonesia' => $bulanIndonesia,
            'logoBase64' => $logoBase64
        ]);

        // Set paper size dan orientation
        $pdf->setPaper('a4', 'portrait');

        // Generate filename
        $filename = 'SP3M_' . str_replace(['/', ' '], '_', $sp3m->nomor_sp3m) . '.pdf';

        // Return PDF download
        return $pdf->download($filename);
    }
}
