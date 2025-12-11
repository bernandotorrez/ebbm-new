<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SP3M - {{ $sp3m->nomor_sp3m }}</title>
    <style>
        @page {
            margin: 2cm 1.5cm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .header-left {
            display: table-cell;
            width: 15%;
            vertical-align: middle;
            text-align: center;
        }
        
        .header-center {
            display: table-cell;
            width: 70%;
            vertical-align: middle;
            text-align: center;
        }
        
        .header-right {
            display: table-cell;
            width: 15%;
            vertical-align: middle;
            text-align: right;
        }
        
        .logo {
            width: 80px;
            height: auto;
        }
        
        .header-title {
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 5px;
        }
        
        .header-subtitle {
            font-size: 9pt;
            margin: 2px 0;
        }
        
        .document-title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin: 20px 0;
            text-decoration: underline;
        }
        
        .document-number {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 20px;
        }
        
        .info-box {
            border: 1px solid #000;
            padding: 10px;
            margin: 15px 0;
            display: table;
            width: 100%;
        }
        
        .info-left {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding-right: 10px;
        }
        
        .info-right {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding-left: 10px;
            border-left: 1px solid #000;
        }
        
        .info-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .info-item {
            margin: 3px 0;
        }
        
        .content {
            margin: 20px 0;
        }
        
        .content-label {
            display: inline-block;
            width: 80px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        table, th, td {
            border: 1px solid #000;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            padding: 8px;
            text-align: center;
        }
        
        td {
            padding: 8px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .notes {
            margin: 15px 0;
        }
        
        .notes-item {
            margin: 5px 0;
        }
        
        .signature-section {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature-box {
            display: inline-block;
            text-align: center;
            min-width: 200px;
        }
        
        .signature-space {
            height: 80px;
        }
        
        .footer-note {
            margin-top: 20px;
            font-size: 10pt;
        }
        
        .footer-note-title {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <!-- Logo BASARNAS (jika ada) -->
            </div>
            <div class="header-center">
                <div class="header-title">BADAN NASIONAL PENCARIAN DAN PERTOLONGAN</div>
                <div class="header-subtitle">Jl. Angkasa Blok B. 15</div>
                <div class="header-subtitle">Kav. 2-3 Jakarta 10720</div>
                <div class="header-subtitle">Telp. : (021) 65701116 / 65867510</div>
                <div class="header-subtitle">Fax : (021) 65701152</div>
                <div class="header-subtitle">Emergency : 115 – (021) 65867511</div>
                <div class="header-subtitle">Emergency Fax : (021) 65867512</div>
                <div class="header-subtitle">E-mail : basarnas@basarnas.go.id</div>
            </div>
            <div class="header-right">
                <!-- Space for additional info -->
            </div>
        </div>
    </div>

    <!-- Document Title -->
    <div class="document-title">
        SURAT PERINTAH PELAKSANAAN PENGAMBILAN BAHAN BAKAR MINYAK
    </div>
    <div class="document-number">
        NOMOR : {{ $sp3m->nomor_sp3m }}
    </div>

    <!-- Info Boxes -->
    <div class="info-box">
        <div class="info-left">
            <div class="info-title">Pertimbangan :</div>
            <div class="info-item">
                Bahwa perlu segera menyalurkan BBM Penggantian Penggantian Rutin
            </div>
            <div class="info-item">Dalam Bulan : {{ \Carbon\Carbon::parse($sp3m->tanggal_sp3m)->format('F Y') }}</div>
            <div class="info-item">Tahun Anggaran : {{ $sp3m->tahun_anggaran }}</div>
            <div class="info-item">Pendistribusian : {{ $sp3m->tbbm->depot ?? '-' }}</div>
        </div>
        <div class="info-right">
            <div class="info-title">Dasar :</div>
            <div class="info-item">
                Perjanjian Kerja Sama antara Badan Nasional Pencarian dan Pertolongan dengan PT. Pertamina Patra Niaga (Persero)
            </div>
            <div class="info-item">No : [Nomor Perjanjian]</div>
            <div class="info-item">Tanggal : {{ \Carbon\Carbon::parse($sp3m->tanggal_sp3m)->format('d F Y') }}</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div style="text-align: center; font-weight: bold; margin: 15px 0;">DIPERINTAHKAN</div>
        
        <div class="info-item">
            <span class="content-label">Kepada</span>: Kepala Kantor Pencarian dan Pertolongan {{ $sp3m->kantorSar->kantor_sar ?? '-' }}.
        </div>
        <div class="info-item">
            <span class="content-label">Untuk</span>:
        </div>
        <div class="info-item" style="margin-left: 80px;">
            Mengambil BBM Penggantian Rutin {{ $sp3m->alpal->alpal ?? '-' }} {{ $sp3m->kantorSar->kantor_sar ?? '-' }} perincian sebagai berikut :
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th rowspan="2">No.</th>
                    <th rowspan="2">Jenis BBM</th>
                    <th colspan="2">Volume</th>
                    <th colspan="2">Harga (Rp)</th>
                </tr>
                <tr>
                    <th>Satuan</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td>{{ $sp3m->bekal->bekal ?? '-' }}</td>
                    <td class="text-center">Liter</td>
                    <td class="text-right">{{ number_format($sp3m->qty, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($sp3m->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($sp3m->jumlah_harga, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="text-right" style="font-weight: bold;">Jumlah Biaya</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($sp3m->jumlah_harga, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Notes -->
        <div class="notes">
            <div class="notes-item">Terbilang : <strong>{{ ucwords(terbilang($sp3m->jumlah_harga)) }} rupiah.</strong></div>
            <div class="notes-item">2. Mengurus pengambilannya dari Depot Pertamina di Terminal BBM {{ $sp3m->tbbm->depot ?? '-' }}.</div>
            <div class="notes-item">3. Menyelesaikan administrasi dan melaporkan hasil pelaksanaan Surat Perintah ini.</div>
        </div>
    </div>

    <!-- Signature -->
    <div class="signature-section">
        <div class="signature-box">
            <div>Dikeluarkan di : Jakarta</div>
            <div>Tanggal : {{ \Carbon\Carbon::parse($sp3m->tanggal_sp3m)->format('d F Y') }}</div>
            <div>Direktur Sarana dan Prasarana,</div>
            <div class="signature-space"></div>
            <div style="font-weight: bold;">Dr. A.M. Alkaf, S.E., M.M., Ph.D</div>
            <div>Marsekal Pertama TNI.</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-note">
        <div class="footer-note-title">Tembusan :</div>
        <div>1. Deputi Bidang Sarana Prasarana dan Sistem Komunikasi;</div>
        <div>2. PT. Pertamina Patra Niaga.</div>
    </div>
</body>
</html>

@php
function terbilang($angka) {
    $angka = abs($angka);
    $baca = array('', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas');
    $terbilang = '';
    
    if ($angka < 12) {
        $terbilang = ' ' . $baca[$angka];
    } elseif ($angka < 20) {
        $terbilang = terbilang($angka - 10) . ' belas';
    } elseif ($angka < 100) {
        $terbilang = terbilang($angka / 10) . ' puluh' . terbilang($angka % 10);
    } elseif ($angka < 200) {
        $terbilang = ' seratus' . terbilang($angka - 100);
    } elseif ($angka < 1000) {
        $terbilang = terbilang($angka / 100) . ' ratus' . terbilang($angka % 100);
    } elseif ($angka < 2000) {
        $terbilang = ' seribu' . terbilang($angka - 1000);
    } elseif ($angka < 1000000) {
        $terbilang = terbilang($angka / 1000) . ' ribu' . terbilang($angka % 1000);
    } elseif ($angka < 1000000000) {
        $terbilang = terbilang($angka / 1000000) . ' juta' . terbilang($angka % 1000000);
    } elseif ($angka < 1000000000000) {
        $terbilang = terbilang($angka / 1000000000) . ' milyar' . terbilang(fmod($angka, 1000000000));
    } elseif ($angka < 1000000000000000) {
        $terbilang = terbilang($angka / 1000000000000) . ' trilyun' . terbilang(fmod($angka, 1000000000000));
    }
    
    return trim($terbilang);
}
@endphp
