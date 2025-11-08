<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
}