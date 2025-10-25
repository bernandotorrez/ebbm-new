<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrder extends Model
{
    use SoftDeletes;

    protected $table = 'tx_do';
    protected $primaryKey = 'do_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'do_id',
        'sp3m_id',
        'tbbm_id',
        'tanggal_do',
        'tahun_anggaran',
        'nomor_do',
        'qty',
        'harga_satuan',
        'ppn',
        'pbbkb',
        'jumlah_harga',
        'file_upload_do',
        'file_upload_laporan',
    ];

    public function sp3m()
    {
        return $this->belongsTo(Sp3m::class, 'sp3m_id', 'sp3m_id');
    }

    public function tbbm()
    {
        return $this->belongsTo(Tbbm::class, 'tbbm_id', 'tbbm_id');
    }
}
