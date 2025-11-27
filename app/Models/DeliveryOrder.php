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
        'harga_bekal_id',
        'tanggal_do',
        'tahun_anggaran',
        'nomor_do',
        'qty',
        'harga_satuan',
        'jumlah_harga',
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

    public function hargaBekal()
    {
        return $this->belongsTo(HargaBekal::class, 'harga_bekal_id', 'harga_bekal_id');
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