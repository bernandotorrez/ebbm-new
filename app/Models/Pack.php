<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PreventUpdateTimestamp;

class Pack extends Model
{
    use SoftDeletes, PreventUpdateTimestamp;

    protected $table = 'ms_pack';
    protected $primaryKey = 'pack_id';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'pack_id',
        'nama_pack',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}