<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;
use App\Traits\HasIsActive;

class GolonganBbm extends Model
{
    use SoftDeletes, PreventUpdateTimestamp, HasIsActive;

    protected $table = 'ms_golongan_bbm';
    protected $primaryKey = 'golongan_bbm_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'golongan_bbm_id',
        'golongan',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_active',
    ];
}