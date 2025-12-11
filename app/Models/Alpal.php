<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;
use App\Traits\HasIsActive;

class Alpal extends Model
{
    use SoftDeletes, PreventUpdateTimestamp, HasIsActive;

    protected $table = 'tx_alpal';
    protected $primaryKey = 'alpal_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'alpal_id',
        'kode_alut',
        'kantor_sar_id',
        'golongan_bbm_id',
        'pos_sandar_id',
        'alpal',
        'ukuran',
        'kapasitas',
        'rob',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_active',
    ];

    public function kantorSar()
    {
        return $this->belongsTo(KantorSar::class, 'kantor_sar_id', 'kantor_sar_id');
    }

    public function golonganBbm()
    {
        return $this->belongsTo(GolonganBbm::class, 'golongan_bbm_id', 'golongan_bbm_id');
    }

    public function posSandar()
    {
        return $this->belongsTo(PosSandar::class, 'pos_sandar_id', 'pos_sandar_id');
    }

    public function tbbm()
    {
        return $this->belongsTo(Tbbm::class, 'tbbm_id', 'tbbm_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'alpal_id', 'alpal_id');
    }

    public function sp3ms()
    {
        return $this->hasMany(Sp3m::class, 'alpal_id', 'alpal_id');
    }

    public function txSp3ks()
    {
        return $this->hasMany(TxSp3k::class, 'alpal_id', 'alpal_id');
    }

    public function pemakaians()
    {
        return $this->hasMany(Pemakaian::class, 'alpal_id', 'alpal_id');
    }
}