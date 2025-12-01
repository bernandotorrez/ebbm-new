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
        'tbbm_id',
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

    public function tbbm()
    {
        return $this->belongsTo(Tbbm::class, 'tbbm_id', 'tbbm_id');
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
        // Jika ada harga_bekal_id, gunakan itu
        if ($this->harga_bekal_id) {
            $hargaBekal = HargaBekal::find($this->harga_bekal_id);
            return $hargaBekal ? $hargaBekal->harga : 0;
        }
        
        // Fallback ke cara lama jika harga_bekal_id tidak ada
        $hargaBekal = HargaBekal::where('kota_id', $this->kota_id)
            ->where('bekal_id', $this->bekal_id)
            ->orderBy('tanggal_update', 'desc')
            ->first();
        
        return $hargaBekal ? $hargaBekal->harga : 0;
    }

    // Helper method to get jumlah_harga
    public function getJumlahHargaAttribute()
    {
        return $this->qty * $this->harga;
    }

    protected static function boot()
    {
        parent::boot();

        // Event saat DO akan dihapus
        static::deleting(function ($deliveryOrder) {
            // Kembalikan sisa_qty ke SP3M
            $sp3m = Sp3m::find($deliveryOrder->sp3m_id);
            if ($sp3m) {
                $sp3m->sisa_qty += $deliveryOrder->qty;
                $sp3m->save();
                
                // Kurangi rob di alpal
                $alpal = Alpal::find($sp3m->alpal_id);
                if ($alpal) {
                    $alpal->rob -= $deliveryOrder->qty;
                    $alpal->save();
                }
            }
        });
    }
}