<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewLaporanDetailTagihan extends Model
{
    protected $table = 'view_laporan_detail_tagihan';
    
    protected $primaryKey = 'do_id';
    
    public $timestamps = false;
    
    protected $casts = [
        'do_id' => 'integer',
        'sp3m_id' => 'integer',
        'kantor_sar_id' => 'integer',
        'tanggal_isi' => 'date',
        'qty' => 'integer',
        'harga_per_liter' => 'decimal:2',
        'jumlah_harga' => 'decimal:2',
        'ppn_11' => 'decimal:2',
        'ppkb' => 'decimal:2',
        'total_ppn_ppkb' => 'decimal:2',
        'total_setelah_pembulatan' => 'decimal:2',
        'jumlah_pembulatan' => 'decimal:2',
    ];
}
