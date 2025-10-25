<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KantorSar extends Model
{
    use SoftDeletes;

    protected $table = 'ms_kantor_sar';
    protected $primaryKey = 'kantor_sar_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'kantor_sar_id',
        'kantor_sar',
    ];
}
