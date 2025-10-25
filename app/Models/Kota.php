<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kota extends Model
{
    use SoftDeletes;

    protected $table = 'ms_kota';
    protected $primaryKey = 'kota_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'kota_id',
        'kota',
    ];
}
