<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class Sp3m extends Model
{
    use SoftDeletes, PreventUpdateTimestamp;

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
        'tbbm_id',
        'nomor_sp3m',
        'tanggal_sp3m',
        'tahun_anggaran',
        'tw',
        'qty',
        'sisa_qty',
        'harga_satuan',
        'jumlah_harga',
        'file_upload_sp3m',
        'file_upload_kelengkapan_sp3m',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_sp3m' => 'date',
        'tbbm_id' => 'integer',
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

    public function tbbm()
    {
        return $this->belongsTo(Tbbm::class, 'tbbm_id', 'tbbm_id');
    }
}