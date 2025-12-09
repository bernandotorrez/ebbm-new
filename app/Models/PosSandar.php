<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class PosSandar extends Model
{
    use SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'ms_pos_sandar';
    protected $primaryKey = 'pos_sandar_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'pos_sandar_id',
        'pos_sandar',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function alpals()
    {
        return $this->hasMany(Alpal::class, 'pos_sandar_id', 'pos_sandar_id');
    }
}