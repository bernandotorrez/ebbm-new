<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sp3m extends Model
{
    use SoftDeletes;

    protected $table = 'tx_sp3m';
    protected $primaryKey = 'sp3m_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'sp3m_id',
        'alpal_id',
        'kantor_sar_id',
        'bekal_id',
        'nomor_sp3m',
        'tahun_anggaran',
        'tw',
        'qty',
        'harga_satuan',
        'jumlah_harga',
        'file_upload_sp3m',
        'file_upload_kelengkapan_sp3m',
    ];

    public function kantorSar()
    {
        return $this->belongsTo(KantorSar::class, 'kantor_sar_id', 'kantor_sar_id');
    }

    public function alpal()
    {
        return $this->belongsTo(Alpal::class, 'alpal_id', 'alpal_id');
    }

    public function bekal()
    {
        return $this->belongsTo(Bekal::class, 'bekal_id', 'bekal_id');
    }
}
