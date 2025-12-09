<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class Tbbm extends Model
{
    use SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'ms_tbbm';
    protected $primaryKey = 'tbbm_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'tbbm_id',
        'kota_id',
        'plant',
        'depot',
        'pbbkb',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function kota()
    {
        return $this->belongsTo(Kota::class, 'kota_id', 'kota_id');
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class, 'tbbm_id', 'tbbm_id');
    }
}