<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kemasan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_kemasan';
    protected $primaryKey = 'kemasan_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'kemasan_liter',
        'kemasan_pack',
    ];

    protected $casts = [
        'kemasan_liter' => 'integer',
    ];
}