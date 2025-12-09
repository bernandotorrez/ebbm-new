<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class KantorSar extends Model
{
    use SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'ms_kantor_sar';
    protected $primaryKey = 'kantor_sar_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'kantor_sar_id',
        'kantor_sar',
        'kota_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    
    // Relationships
    public function kota()
    {
        return $this->belongsTo(Kota::class, 'kota_id', 'kota_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'kantor_sar_id', 'kantor_sar_id');
    }

    public function alpals()
    {
        return $this->hasMany(Alpal::class, 'kantor_sar_id', 'kantor_sar_id');
    }

    public function sp3ms()
    {
        return $this->hasMany(Sp3m::class, 'kantor_sar_id', 'kantor_sar_id');
    }

    public function txSp3ks()
    {
        return $this->hasMany(TxSp3k::class, 'kantor_sar_id', 'kantor_sar_id');
    }

    public function pemakaians()
    {
        return $this->hasMany(Pemakaian::class, 'kantor_sar_id', 'kantor_sar_id');
    }

    public function posSandars()
    {
        return $this->hasMany(PosSandar::class, 'kantor_sar_id', 'kantor_sar_id');
    }
}