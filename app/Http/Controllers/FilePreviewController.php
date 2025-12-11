<?php

namespace App\Http\Controllers;

use App\Models\Sp3mLampiran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilePreviewController extends Controller
{
    /**
     * Preview file lampiran SP3M
     * Memerlukan autentikasi untuk akses file
     */
    public function previewSp3mLampiran($id)
    {
        // Cek autentikasi
        if (!auth()->check()) {
            abort(403, 'Unauthorized access');
        }

        // Ambil data lampiran
        $lampiran = Sp3mLampiran::findOrFail($id);

        // Cek apakah file exists
        if (!Storage::disk('public')->exists($lampiran->file_path)) {
            abort(404, 'File not found');
        }

        // Get file path
        $filePath = Storage::disk('public')->path($lampiran->file_path);
        
        // Get mime type
        $mimeType = Storage::disk('public')->mimeType($lampiran->file_path);
        
        // Return file response
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($lampiran->file_path) . '"',
        ]);
    }

    /**
     * Download file lampiran SP3M
     * Memerlukan autentikasi untuk download file
     */
    public function downloadSp3mLampiran($id)
    {
        // Cek autentikasi
        if (!auth()->check()) {
            abort(403, 'Unauthorized access');
        }

        // Ambil data lampiran
        $lampiran = Sp3mLampiran::findOrFail($id);

        // Cek apakah file exists
        if (!Storage::disk('public')->exists($lampiran->file_path)) {
            abort(404, 'File not found');
        }

        // Get file path
        $filePath = Storage::disk('public')->path($lampiran->file_path);
        
        // Get original filename
        $filename = $lampiran->nama_file . '.' . pathinfo($lampiran->file_path, PATHINFO_EXTENSION);
        
        // Return download response
        return response()->download($filePath, $filename);
    }
}
