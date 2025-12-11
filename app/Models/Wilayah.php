<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;
use App\Traits\HasIsActive;

class Wilayah extends Model
{
    use SoftDeletes, PreventUpdateTimestamp, HasIsActive;

    protected $table = 'ms_wilayah';
    protected $primaryKey = 'wilayah_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'wilayah_id',
        'wilayah_ke',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_active',
    ];

    public function kotas(): HasMany
    {
        return $this->hasMany(Kota::class, 'wilayah_id');
    }
}