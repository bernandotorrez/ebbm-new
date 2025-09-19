<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GolonganBbm extends Model
{
    use SoftDeletes;

    protected $table = 'golongan_bbms';

    protected $primaryKey = 'golongan_bbm_id';

    protected $fillable = [
        'golongan',
    ];
}
