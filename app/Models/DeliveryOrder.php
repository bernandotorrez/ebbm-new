<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrder extends Model
{
    use SoftDeletes;

    protected $table = 'delivery_orders';
    protected $primaryKey = 'do_id';

    protected $fillable = [
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
        return $this->belongsTo(Sp3m::class, 'sp3m_id');
    }

    public function tbbm()
    {
        return $this->belongsTo(Tbbm::class, 'tbbm_id');
    }
}
