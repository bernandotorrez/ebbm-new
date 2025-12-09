<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class Kota extends Model
{
    use SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'ms_kota';
    protected $primaryKey = 'kota_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'kota_id',
        'kota',
        'wilayah_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function tbbms(): HasMany
    {
        return $this->hasMany(Tbbm::class, 'kota_id', 'kota_id');
    }

    public function kantorSars(): HasMany
    {
        return $this->hasMany(KantorSar::class, 'kota_id', 'kota_id');
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class, 'kota_id', 'kota_id');
    }
}