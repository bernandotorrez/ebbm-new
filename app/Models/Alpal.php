<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alpal extends Model
{
    use SoftDeletes;

    protected $table = 'tx_alpal';
    protected $primaryKey = 'alpal_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'alpal_id',
        'kantor_sar_id',
        'tbbm_id',
        'pos_sandar_id',
        'alpal',
        'ukuran',
        'kapasitas',
        'rob',
    ];

    public function kantorSar()
    {
        return $this->belongsTo(KantorSar::class, 'kantor_sar_id', 'kantor_sar_id');
    }

    public function posSandar()
    {
        return $this->belongsTo(PosSandar::class, 'pos_sandar_id', 'pos_sandar_id');
    }

    public function tbbm()
    {
        return $this->belongsTo(Tbbm::class, 'tbbm_id', 'tbbm_id');
    }
}
