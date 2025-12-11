<?php

namespace App\Http\Controllers;

use App\Models\Sp3m;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class Sp3mPdfController extends Controller
{
    /**
     * Export SP3M to PDF
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

        // Load view PDF
        $pdf = Pdf::loadView('pdf.sp3m', [
            'sp3m' => $sp3m
        ]);

        // Set paper size dan orientation
        $pdf->setPaper('a4', 'portrait');

        // Generate filename
        $filename = 'SP3M_' . str_replace(['/', ' '], '_', $sp3m->nomor_sp3m) . '.pdf';

        // Return PDF download
        return $pdf->download($filename);
    }
}
