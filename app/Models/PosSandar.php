<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;
use App\Traits\HasIsActive;

class PosSandar extends Model
{
    use SoftDeletes, PreventUpdateTimestamp, HasIsActive;

    protected $table = 'ms_pos_sandar';
    protected $primaryKey = 'pos_sandar_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'pos_sandar_id',
        'pos_sandar',
        'kantor_sar_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_active',
    ];

    protected $casts = [
        'kantor_sar_id' => 'integer',
    ];

    public function kantorSar(): BelongsTo
    {
        return $this->belongsTo(KantorSar::class, 'kantor_sar_id', 'kantor_sar_id');
    }

    public function alpals(): HasMany
    {
        return $this->hasMany(Alpal::class, 'pos_sandar_id', 'pos_sandar_id');
    }
}