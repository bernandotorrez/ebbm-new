<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class DeliveryOrder extends Model
{
    use SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'tx_do';
    protected $primaryKey = 'do_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'do_id',
        'sp3m_id',
        'bekal_id',
        'kota_id',
        'harga_bekal_id',
        'tanggal_do',
        'tahun_anggaran',
        'nomor_do',
        'qty',
        'file_upload_do',
        'file_upload_laporan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function sp3m()
    {
        return $this->belongsTo(Sp3m::class, 'sp3m_id', 'sp3m_id');
    }

    public function bekal()
    {
        return $this->belongsTo(Bekal::class, 'bekal_id', 'bekal_id');
    }

    public function kota()
    {
        return $this->belongsTo(Kota::class, 'kota_id', 'kota_id');
    }

    public function hargaBekal()
    {
        return $this->belongsTo(HargaBekal::class, 'harga_bekal_id', 'harga_bekal_id');
    }

    // Helper method to get harga from ms_harga_bekal
    public function getHargaAttribute()
    {
        // Hanya gunakan harga_bekal_id, tidak ada fallback
        if ($this->harga_bekal_id) {
            $hargaBekal = HargaBekal::find($this->harga_bekal_id);
            return $hargaBekal ? $hargaBekal->harga : 0;
        }
        
        // Jika tidak ada harga_bekal_id, return 0
        return 0;
    }

    // Helper method to get jumlah_harga
    public function getJumlahHargaAttribute()
    {
        return $this->qty * $this->harga;
    }

    // Helper method to get tbbm through sp3m
    public function getTbbmAttribute()
    {
        return $this->sp3m?->tbbm;
    }
}