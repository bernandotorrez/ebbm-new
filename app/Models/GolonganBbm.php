<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class GolonganBbm extends Model
{
    use SoftDeletes, PreventUpdateTimestamp;

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
    ];
}