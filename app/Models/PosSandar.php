<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosSandar extends Model
{
    use SoftDeletes;

    protected $table = 'ms_pos_sandar';
    protected $primaryKey = 'pos_sandar_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'pos_sandar_id',
        'pos_sandar',
    ];
}
