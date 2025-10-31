<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wilayah extends Model
{
    use SoftDeletes;

    protected $table = 'ms_wilayah';
    protected $primaryKey = 'wilayah_id';
    public $timestamps = true;
    protected $keyType = 'bigint';
    public $incrementing = false;

    protected $fillable = [
        'wilayah_id',
        'wilayah_ke',
    ];
}