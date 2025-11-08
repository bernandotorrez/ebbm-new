<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class Kemasan extends Model
{
    use HasFactory, SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'ms_kemasan';
    protected $primaryKey = 'kemasan_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'kemasan_liter',
        'kemasan_pack',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'kemasan_liter' => 'integer',
    ];
}