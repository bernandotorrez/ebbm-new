<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class Satuan extends Model
{
    use SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'ms_satuan';
    protected $primaryKey = 'satuan_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'satuan_id',
        'satuan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}