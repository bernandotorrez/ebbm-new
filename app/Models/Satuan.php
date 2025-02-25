<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Satuan extends Model
{
    use SoftDeletes;

    protected $table = 'satuans';

    protected $primaryKey = 'satuan_id';

    protected $fillable = [
        'satuan',
    ];
}
